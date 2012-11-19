<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is 
// in an sdk sub-directory relative to this file
require_once dirname(__FILE__) . '/vendor/amazonwebservices/aws-sdk-for-php/sdk.class.php';

// Instantiate the class.
$dynamodb = new AmazonDynamoDB(array(
                'key' => getenv('AWS_ACCESS_KEY'),
                'secret' => getenv('AWS_SECRET_KEY'),
                ));
$dynamodb->set_region('dynamodb.'.getenv('AWS_REGION').'.amazonaws.com');

$fourteen_days_ago = date('Y-m-d H:i:s', strtotime("-14 days"));
	
$response = $dynamodb->query(array(
    'TableName' => 'Reply',
    'HashKeyValue' => array(
        AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2',
    ),
    'RangeKeyCondition' => array(
        'ComparisonOperator' => AmazonDynamoDB::CONDITION_GREATER_THAN_OR_EQUAL,
        'AttributeValueList' => array(
            array(
                AmazonDynamoDB::TYPE_STRING => $fourteen_days_ago
            )
        )
    )
));

header('content-type: text/plain');
// Response code 200 indicates success
print_r($response);
	
?>

