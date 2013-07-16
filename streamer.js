/**
 * Module dependencies.
 */
var config = require('./config');

var io = require('socket.io').listen(config.ws_port);
var sys = require('util');
var TwitterStreamClient = require(config.twitter_stream_client).TwitterStreamClient;

var dynode = require('dynode');
var redis = require('redis');

var redis_host = config.redis_host;
var redis_port = config.redis_port;

var dynamodb = new dynode.Client({
	'region': config.aws_region,
	'accessKeyId': config.aws_access_key,
	'secretAccessKey': config.aws_secret_key
});

var publisher = redis.createClient(redis_port, redis_host);

var twitterStreamClients = {};
var publicStreamClients = {};
var pubStreamUrl = "https://stream.twitter.com/1/statuses/sample.json";
var userStreamUrl = "https://userstream.twitter.com/2/user.json"

io.sockets.on('connection', function(socket){
	socket.emit('who', {});
	socket.on('i am', function (userId){
		console.log('subscribing to user update channel ' + userId);
 		socket.subscriber = redis.createClient(redis_port, redis_host);
		socket.subscriber.subscribe(userId);
		socket.subscriber.on("message", function(channel, message) {
			socket.emit('feed', JSON.parse(message));
		});
		var client = getClient(userId);
		client.init.call(client);
	});
	socket.on('setPubStreamRate', function (args){
		console.log('setting pubstream rate for user' + args.user);
		if(args.user === undefined || args.rate === undefined){
			console.error("Invalid args: " + args);
			return;
		}
		var client = getClient(args.user);
		client.setRate.call(client, args.rate);
	});
	socket.on('disconnect', function(){
		if(socket.subscriber != null){
			socket.subscriber.unsubscribe();
			socket.subscriber.end();
		}
	});
});

function getClient(user) {
	var client = publicStreamClients[user];
	if(client == null){
		client = createClient(user)
		publicStreamClients[user] = client;
	}
	return client;
}

function createClient(user){
	return new TwitterStreamClient(user, pubStreamUrl, storeFeed);
}

function storeFeed(user, feed){
	console.log(feed);
	var writeRequests = {};
	writeRequests[config.user_feeds_table] = [
      			{put : {id: user, time: feed.time, messageId: feed.id }}
    		];
	writeRequests[config.feeds_table] = [
      			{put : feed}
    		];

	dynamodb.batchWriteItem(writeRequests, function(error, meta){
		if(error){
			console.error(error);
			feed['error'] = 1;
		} 
		notifyUpdate(user, feed);
		console.log(meta);
	});
}

function getTwitterToken(user, callback){
	dynamodb.getItem(config.user_data_table, user, {AttributesToGet: ['twitter_token', 'twitter_token_secret']}, function(obj, data, meta){
		if(data != null){
			callback(data);	
		} else {
			console.error(user + " does not has authenticated with Twittter");
		}
	});
}

function notifyUpdate(user, feed){
	publisher.publish(user, JSON.stringify(feed));
}
