<?php

class DBHelper {
	const TABLE_FEEDS = "soag_user_feeds";
	const TABLE_STORIES = "soag_feeds";
	const TABLE_USERS = "soag_users";
	const FACEBOOK_TOKEN = 'facebook_token';
	const TWITTER_TOKEN = 'twitter_token';
	const TWITTER_TOKEN_SECRET = 'twitter_token_secret';

	const RESULT_CODE = "code";
	const RESULT_STORED = "stored";
	const SUCCESS = 200;
	const ERROR = 500;
	
	private $dynamodb;
	private $rediska;
	
	function __construct() {
		global $aws_access_key, $aws_secret_key, $redis_server, $redis_port;
		$this->dynamodb = new AmazonDynamoDB(array(
				'key' => $aws_access_key,
				'secret' => $aws_secret_key
		));
		$this->dynamodb->set_region(AmazonDynamoDB::REGION_APAC_NE1);
		
		$this->rediska = new Rediska(array(
					'servers'   => array(
       					array('host' => $redis_server, 'port' => $redis_port)
							)
				));

		$this->init();
	}
	
	function init(){
		if(! $this->exists(self::TABLE_FEEDS)){
			error_log(self::TABLE_FEEDS." does not exist. Will create");
			$this->createHashRangeTable(self::TABLE_FEEDS, 'id', 'time', 1, 1);
		}
		if(! $this->exists(self::TABLE_STORIES)){
			error_log(self::TABLE_STORIES." does not exist. Will create");
			$this->createHashTable(self::TABLE_STORIES, 'id', 1, 1);
		}
		if(! $this->exists(self::TABLE_USERS)){
			error_log(self::TABLE_USERS." does not exist. Will create");
			$this->createHashTable(self::TABLE_USERS, 'id', 1, 1);
		}
	}
	
	public function storeFeed($user, $feed){
		$feedItem = $this->genPutRequestForFeed($user, $feed);
		$storyItem = $this->genPutRequestForStory($feed);
		$request = array(
				'RequestItems' => array(
						self::TABLE_FEEDS => array($feedItem),
						self::TABLE_STORIES => array($storyItem)
				)
		);
		$retryCnt = 0;
		$backoff_ms = 20;
		$response = $this->dynamodb->batch_write_item($request);
		if(! empty($response->body->UnprocessedItems)){
			if(++$retryCnt >= 3){
				error_log("Failed to store feed: " + $feed);
				return FALSE;
			}
			error_log("Unprocessed items exist. Will retry in ".$backoff_ms." ms");
			usleep($backoff_ms * 1000);
			
			$request = array('RequestItems' => $response->body->UnprocessedItems);
			$response = $this->dynamodb->batch_write_item($request);

			$backoff_ms = $backoff_ms * 2;
		}
		return TRUE;
	}
	
	private function genPutRequestForFeed($user, $feed){
		return array(
				'PutRequest' => array(
						'Item' => $this->dynamodb->attributes(array(
							'id' => $user,
							'time' => $feed['time'],
							'messageId' => $feed['id']
							))
				));
	}

	private function genPutRequestForStory($feed){
		return array(
				'PutRequest' => array(
						'Item' => $this->dynamodb->attributes($feed)
				));
	}
	
	public function getFeeds($user, $limit, $since){
		$response = $this->dynamodb->batch_get_item(array(
				'RequestItems' => array(
						self::TABLE_STORIES => array(
							'Keys' => $this->getKeysToGet($user, $limit, $since)
								)
				)
			));
		$feeds = array();
		foreach($response->body->Responses->{self::TABLE_STORIES}->Items as $item){
			$feed = array();
			foreach($item as $key => $value){
				if(empty($value->S)){
					$feed[$key] = intval($value->N->to_string());
				} else {
					$feed[$key] = $value->S->to_string();
				}
			} 
			array_push($feeds, $feed);
		}
		return $feeds;
	}
	
	private function getKeysToGet($user, $limit, $since){
		$query = array(
				'TableName' => self::TABLE_FEEDS,
				'HashKeyValue' => array(AmazonDynamoDB::TYPE_STRING=>$user));
		if(! empty($since)) $query['RangeKeyCondition'] = array(
				'AttributeValueList'=>array(
						array(AmazonDynamoDB::TYPE_NUMBER=> $since)
				),
				'ComparisonOperator'=>AmazonDynamoDB::CONDITION_GREATER_THAN_OR_EQUAL
		);
		if(! empty($limit)) $query['Limit'] = intval($limit);
		
		$response = $this->dynamodb->query($query);
		$keys = array();
		
		foreach ($response->body->Items as $item){
			$msgId = $item->messageId->S->to_string();
			array_push($keys, array('HashKeyElement' => array(AmazonDynamoDB::TYPE_STRING => $msgId)));
		}
		return $keys;
	}
	
	public function storeFacebookToken($user, $token){
		// Creates the entry if it does not exist 
		$result = $this->dynamodb->put_item(array(
				'TableName' => self::TABLE_USERS,
				'Item' => array(
						'id' => $this->dynamodb->attribute($user),
						self::FACEBOOK_TOKEN => $this->dynamodb->attribute($token)
				),
				'Expected' => array(
						'id' => array( 'Exists' => FALSE )
							)
				));
		if($result->status == 400){ // This means the entry has existed already
			// Updates the Facebook token
			$result = $this->dynamodb->update_item(array(
					'TableName' => self::TABLE_USERS,
					'Key' => array (
							'HashKeyElement' =>  $this->dynamodb->attribute($user)
			 		),
			 	     'AttributeUpdates' => array(
					  	self::FACEBOOK_TOKEN => array('Value' => $this->dynamodb->attribute($token))
						)
				));
		}
	}
	
	public function getFacebookToken($user){
		$data = $this->dynamodb->get_item(array(
				'TableName' => self::TABLE_USERS,
				'Key' => array(
						'HashKeyElement' => $this->dynamodb->attribute($user)
						),
				'AttributesToGet' => array(self::FACEBOOK_TOKEN)
				));
		if($data->body->Item){
			return $data->body->Item->facebook_token->S->to_string();
		} else {
			return null;
		}
	}

	
	public function storeTwitterToken($user, $token){
		$result = $this->dynamodb->update_item(array(
				'TableName' => self::TABLE_USERS,
				'Key' => array(
						'HashKeyElement' => $this->dynamodb->attribute($user)
				),
				'AttributeUpdates' => array(
						self::TWITTER_TOKEN => array('Value' => $this->dynamodb->attribute($token['oauth_token'])),
						self::TWITTER_TOKEN_SECRET => array('Value' => $this->dynamodb->attribute($token['oauth_token_secret']))
					)
		));
	}
	
	public function getTwitterToken($user){
		$this->dynamodb->get_item(array(
				'TableName' => self::TABLE_USERS,
				'Key' => array(
						'HashKeyElement' => $this->dynamodb->attribute($user)
						),
				'AttributesToGet' => array(
						self::TWITTER_TOKEN,
						self::TWITTER_TOKEN_SECRET
						)
				));
	}
	

	public function notifyUpdate($user, $msg){
		$this->rediska->publish($user, $msg);
	}
	
	private function exists($tableName) {

	    $response = $this->dynamodb->describe_table(array(
    	    'TableName' => $tableName,
    	));

	    if((string)$response->body->Table->TableStatus == 'ACTIVE'){
    	    return true;
    	} else {
        	return false;
    	}
	}
	
	private function createHashRangeTable ($tableName, $hashKey, $rangeKey, $readCap, $writeCap){
		$response = $this->dynamodb->create_table(array(
                    'TableName' => $tableName,
                    'KeySchema' => array(
                        'HashKeyElement' => array(
                            'AttributeName' => $hashKey,
                            'AttributeType' => AmazonDynamoDB::TYPE_STRING,
                        ),
                        'RangeKeyElement' => array(
                            'AttributeName' => $rangeKey,
                            'AttributeType' => AmazonDynamoDB::TYPE_STRING,
                        )
                    ),
                    'ProvisionedThroughput' => array(
                        'ReadCapacityUnits' => $readCap,
                        'WriteCapacityUnits' => $writeCap,
                    ),
                ));
		error_log($response->body->message);
	}	
	
	private function createHashTable ($tableName, $hashKey, $readCap, $writeCap){
		$response = $this->dynamodb->create_table(array(
				'TableName' => $tableName,
				'KeySchema' => array(
						'HashKeyElement' => array(
								'AttributeName' => $hashKey,
								'AttributeType' => AmazonDynamoDB::TYPE_STRING,
						),
				),
				'ProvisionedThroughput' => array(
						'ReadCapacityUnits' => $readCap,
						'WriteCapacityUnits' => $writeCap,
				),
		));
		error_log($response->body->message);
	}
	
	
}

?>
