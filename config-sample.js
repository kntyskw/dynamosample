var config = exports;

config.ws_port = 10080;
config.aws_region = "us-east-1";
config.aws_access_key = "AWS_ACCESS_KEY";
config.aws_secret_key = "AWS_SECRET_KEY";
config.redis_host = "localhost";
config.redis_port = 6379;
config.twitter_stream_client = "./dummy_twitter_stream_client";
// Change this as follows if you want to receive real twitter stream and edit credentials
//config.twitter_stream_client = "./twitter_stream_client";
config.twitter_consumer_key = "TWITTER_OAUTH_CONSUMER_KEY";
config.twitter_consumer_secret = "TWITTER_OAUTH_CONSUMER_SECRET";
config.twitter_access_token = null;
config.twitter_access_token_secret = null;



