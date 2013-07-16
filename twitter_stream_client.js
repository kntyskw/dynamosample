var config = require('./config');
Dynode = require('dynode');
var dynamodb = new Dynode.Client({
	'region': config.aws_region,
	'accessKeyId': config.aws_access_key,
	'secretAccessKey': config.aws_secret_key
});


var TwitterStreamClient = function(user, url, callback){
	this.user = user;
	this.url = url;
	this.callback = callback;

	var consumer_key = config.twitter_consumer_key;
	var consumer_secret = config.twitter_consumer_secret;

	var OAuth = require('oauth').OAuth;
	this.oa= new OAuth("https://twitter.com/oauth/request_token",
                 "https://twitter.com/oauth/access_token", 
                 consumer_key, consumer_secret, 
                 "1.0A", "http://localhost:3000/oauth/callback", "HMAC-SHA1");
}

TwitterStreamClient.prototype.init = function(){
	this.access_token = config.twitter_access_token;
	this.access_token_secret = config.twitter_access_token_secret;
	this.mod = 1;
	this.count = 0;
	this.streamHandler = null;
}

TwitterStreamClient.prototype.start = function(){
	if(this.streamHandler != null) return ;
	var client = this;

	if(this.access_token == null || this.access_token_secret == null){
		this.getToken(function(data){
			if(data != null){
				client.access_token = data.twitter_token;
				client.access_token_secret = data.twitter_token_secret;
				client.start();	
			} else {
				console.error("Failed to get access token");
			}
		});
		return;
	};

	console.log(this.url);
	this.streamHandler = this.oa.get(this.url, this.access_token, this.access_token_secret );

	this.streamHandler.addListener('response', function (response) {
		response.setEncoding('utf8');
		response.addListener('data', function (chunk) {
			try {
				var msg = JSON.parse(chunk);
				if(typeof(msg.friends) == 'undefined'){
					var feed = normalizeTwitterFeed(msg);
					if(feed.id){
						if(client.count++ % client.mod == 0){
							client.callback(client.user, feed);
							client.count = 1;
						}
					}
				}
			} catch (e) { }
		});
  		response.addListener('end', function () {
			console.log('response end');
			client.streamHandler = null;
  		});
	});
	this.streamHandler.end('end', function () {
		console.log('end');
	});
}
TwitterStreamClient.prototype.stop = function(){
	if(this.streamHandler != null){
		// DO CHANGE THIS TO STOP
		console.log("Stopping twitter stream client");
		this.streamHandler.abort();
	}
}
TwitterStreamClient.prototype.setRate = function(rate){
	console.log("Setting rate to " + rate);
	if(rate > 0) {
		this.mod = Math.round(1 / rate);
		console.log("Will store 1 in " + this.mod + " messages");
		this.start();
	} else {
		this.stop();
	}
}

TwitterStreamClient.prototype.getToken = function(callback){
	dynamodb.getItem(config.user_data_table, this.user, {AttributesToGet: ['twitter_token', 'twitter_token_secret']}, function(error, data, meta){
		if(error == null){
			callback(data);
		} else {
			console.error(this.user + " does not has authenticated with Twittter");
		}
	});
}

function normalizeTwitterFeed(feed){
	var normalized = {
		'id': feed.id_str,
		'time': Date.parse(feed.created_at) / 1000,
		'message': feed.text,
		'sns': 'twitter'
	};
	if(feed.user){
		normalized.from = feed.user.screen_name;
		normalized.thumb = feed.user.profile_image_url;
	}
	return normalized;
}


exports.TwitterStreamClient = TwitterStreamClient;
