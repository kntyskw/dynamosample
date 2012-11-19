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
				'key' => getenv('AWS_ACCESS_KEY'),
                		'secret' => getenv('AWS_SECRET_KEY')
		));
		$this->dynamodb->set_region('dynamodb.'.getenv('AWS_REGION').'.amazonaws.com');
		$this->rediska = new Rediska(array(
                                        'servers'   => array(
                                        array('host' => $redis_server, 'port' => $redis_port)
                                                        )
                                ));

		
	}
	
	public function storeTwitterToken($user, $token){
		global $user_data_table;
		$result = $this->dynamodb->update_item(array(
				'TableName' => $user_data_table,
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
		global $user_data_table;
		$this->dynamodb->get_item(array(
				'TableName' => $user_data_table,
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
