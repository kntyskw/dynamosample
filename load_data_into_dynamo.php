<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is 
// in an sdk sub-directory relative to this file
require_once dirname(__FILE__) . '/vendor/amazonwebservices/aws-sdk-for-php/sdk.class.php';

// Instantiate the class
$dynamodb = new AmazonDynamoDB(array(
		'key' => getenv('AWS_ACCESS_KEY'),
		'secret' => getenv('AWS_SECRET_KEY'),
		));
$dynamodb->set_region('dynamodb.'.getenv('AWS_REGION').'.amazonaws.com');

####################################################################
# Setup some local variables for dates

$one_day_ago = date('Y-m-d H:i:s', strtotime("-1 days"));
$seven_days_ago = date('Y-m-d H:i:s', strtotime("-7 days"));
$fourteen_days_ago = date('Y-m-d H:i:s', strtotime("-14 days"));
$twenty_one_days_ago = date('Y-m-d H:i:s', strtotime("-21 days"));
 
####################################################################

// Set up batch requests
$queue = new CFBatchRequest();
$queue->use_credentials($dynamodb->credentials);

// Add items to the batch
$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '101'              ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => 'Book 101 Title'   ),
        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '111-1111111111'   ),
        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1')   ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '2'                ),
        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 0.5' ),
        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '500'              ),
        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '1'                ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'             )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '102'                       ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => 'Book 102 Title'            ),
        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '222-2222222222'            ),
        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1', 'Author2') ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '20'                        ),
        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 0.8'          ),
        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '600'                       ),
        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '1'                         ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'                      )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '103'                       ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => 'Book 103 Title'            ),
        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '333-3333333333'            ),
        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1', 'Author2') ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '2000'                      ),
        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 1.5'          ),
        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '600'                       ),
        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '0'                         ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'                      )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '201'                 ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => '18-Bike-201'         ),
        'Description'     => array( AmazonDynamoDB::TYPE_STRING           => '201 Description'     ),
        'BicycleType'     => array( AmazonDynamoDB::TYPE_STRING           => 'Road'                ),
        'Brand'           => array( AmazonDynamoDB::TYPE_STRING           => 'Mountain A'          ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '100'                 ),
        'Gender'          => array( AmazonDynamoDB::TYPE_STRING           => 'M'                   ),
        'Color'           => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Red', 'Black') ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Bicycle'             )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '202'                   ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => '21-Bike-202'           ),
        'Description'     => array( AmazonDynamoDB::TYPE_STRING           => '202 Description'       ),
        'BicycleType'     => array( AmazonDynamoDB::TYPE_STRING           => 'Road'                  ),
        'Brand'           => array( AmazonDynamoDB::TYPE_STRING           => 'Brand-Company A'       ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '200'                   ),
        'Gender'          => array( AmazonDynamoDB::TYPE_STRING           => 'M'                     ),
        'Color'           => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Green', 'Black') ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Bicycle'               )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '203'                          ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => '19-Bike-203'                  ),
        'Description'     => array( AmazonDynamoDB::TYPE_STRING           => '203 Description'              ),
        'BicycleType'     => array( AmazonDynamoDB::TYPE_STRING           => 'Road'                         ),
        'Brand'           => array( AmazonDynamoDB::TYPE_STRING           => 'Brand-Company B'              ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '300'                          ),
        'Gender'          => array( AmazonDynamoDB::TYPE_STRING           => 'W'                            ),
        'Color'           => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Red', 'Green', 'Black') ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Bicycle'                      )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '204'             ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => '18-Bike-204'     ),
        'Description'     => array( AmazonDynamoDB::TYPE_STRING           => '204 Description' ),
        'BicycleType'     => array( AmazonDynamoDB::TYPE_STRING           => 'Mountain'        ),
        'Brand'           => array( AmazonDynamoDB::TYPE_STRING           => 'Brand-Company B' ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '400'             ),
        'Gender'          => array( AmazonDynamoDB::TYPE_STRING           => 'W'               ),
        'Color'           => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Red')      ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Bicycle'         )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '205'                 ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => '20-Bike-205'         ),
        'Description'     => array( AmazonDynamoDB::TYPE_STRING           => '205 Description'     ),
        'BicycleType'     => array( AmazonDynamoDB::TYPE_STRING           => 'Hybrid'              ),
        'Brand'           => array( AmazonDynamoDB::TYPE_STRING           => 'Brand-Company C'     ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '500'                 ),
        'Gender'          => array( AmazonDynamoDB::TYPE_STRING           => 'B'                   ),
        'Color'           => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Red', 'Black') ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Bicycle'             )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Forum',
    'Item' => array(
        'Name'     => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB'     ), // Hash Key
        'Category' => array( AmazonDynamoDB::TYPE_STRING => 'Amazon Web Services' ),
        'Threads'  => array( AmazonDynamoDB::TYPE_NUMBER => '0'                   ),
        'Messages' => array( AmazonDynamoDB::TYPE_NUMBER => '0'                   ),
        'Views'    => array( AmazonDynamoDB::TYPE_NUMBER => '1000'                ),
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Forum',
    'Item' => array(
        'Name'     => array( AmazonDynamoDB::TYPE_STRING => 'Amazon S3'           ), // Hash Key
        'Category' => array( AmazonDynamoDB::TYPE_STRING => 'Amazon Web Services' ),
        'Threads'  => array( AmazonDynamoDB::TYPE_NUMBER => '0'                   )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Reply',
    'Item' => array(
        'Id'            => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 1' ), // Hash Key
        'ReplyDateTime' => array( AmazonDynamoDB::TYPE_STRING => $fourteen_days_ago                 ), // Range Key
        'Message'       => array( AmazonDynamoDB::TYPE_STRING => 'DynamoDB Thread 1 Reply 2 text'    ),
        'PostedBy'      => array( AmazonDynamoDB::TYPE_STRING => 'User B'                            ),
    )
));
$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Reply',
    'Item' => array(
        'Id'            => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2' ), // Hash Key
        'ReplyDateTime' => array( AmazonDynamoDB::TYPE_STRING => $twenty_one_days_ago                    ), // Range Key
        'Message'       => array( AmazonDynamoDB::TYPE_STRING  => 'DynamoDB Thread 2 Reply 3 text'   ),
        'PostedBy'      => array( AmazonDynamoDB::TYPE_STRING => 'User B'                            ),
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Reply',
    'Item' => array(
        'Id'            => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2' ), // Hash Key
        'ReplyDateTime' => array( AmazonDynamoDB::TYPE_STRING => $seven_days_ago                    ), // Range Key
        'Message'       => array( AmazonDynamoDB::TYPE_STRING  => 'DynamoDB Thread 2 Reply 2 text'   ),
        'PostedBy'      => array( AmazonDynamoDB::TYPE_STRING => 'User A'                            ),
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'Reply',
    'Item' => array(
        'Id'            => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2' ), // Hash Key
        'ReplyDateTime' => array( AmazonDynamoDB::TYPE_STRING => $one_day_ago                       ), // Range Key
        'Message'       => array( AmazonDynamoDB::TYPE_STRING  => 'DynamoDB Thread 2 Reply 1 text'   ),
        'PostedBy'      => array( AmazonDynamoDB::TYPE_STRING => 'User A'                            ),
    )
));
     
// Execute the batch of requests in parallel
$responses = $dynamodb->batch($queue)->send();
     
// Check for success...
if ($responses->areOK())
{
    echo "The data has been successfully added to the table." . PHP_EOL;
}
    else
{
    echo "Error: Failed to load data." . PHP_EOL;
    print_r($responses);
}
?>
