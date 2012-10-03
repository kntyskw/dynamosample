<<<<<<< HEAD
/**
 * Module dependencies.
 */
var config = require('./config');

var io = require('socket.io').listen(config.ws_port);
=======

/**
 * Module dependencies.
 */

var io = require('socket.io').listen(10080);
>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47
var sys = require('util');
var OAuth= require('oauth').OAuth;
var dynode = require('dynode');
var redis      = require('redis');

<<<<<<< HEAD

var consumer_key = config.twitter_consumer_key;
var consumer_secret = config.twitter_consumer_secret;
var redis_host = config.redis_host;
var redis_port = config.redis_port;


var dynamodb = new dynode.Client({
	'accessKeyId': config.aws_access_key,
	'secretAccessKey': config.aws_secret_key
});

var publisher = redis.createClient(redis_port, redis_host);
=======
var consumer_key = '';
var consumer_secret = '';

var dynamodb = new dynode.Client({
	'accessKeyId': "",
	'secretAccessKey': ""
});

var publisher = redis.createClient(6379, 'localhost');
>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47

oa= new OAuth("https://twitter.com/oauth/request_token",
                 "https://twitter.com/oauth/access_token", 
                 consumer_key, consumer_secret, 
                 "1.0A", "http://localhost:3000/oauth/callback", "HMAC-SHA1");


<<<<<<< HEAD
=======
var app = express();

app.configure(function(){
  app.set('port', process.env.PORT || 3000);
  app.set('views', __dirname + '/views');
  app.set('view engine', 'jade');
  app.use(express.favicon());
  app.use(express.logger('dev'));
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(app.router);
  app.use(express.static(path.join(__dirname, 'public')));
});

app.configure('development', function(){
  app.use(express.errorHandler());
});

app.get('/', routes.index);

http.createServer(app).listen(app.get('port'), function(){
  console.log("Express server listening on port " + app.get('port'));
});

>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47
var twitterStreamClients = {};
var publicStreamClients = {};
var pubStreamUrl = "https://stream.twitter.com/1/statuses/sample.json";
var userStreamUrl = "https://userstream.twitter.com/2/user.json"

io.sockets.on('connection', function(socket){
	socket.emit('who', {});
	socket.on('i am', function (userId){
		console.log('subscribing to user update channel ' + userId);
<<<<<<< HEAD
 		socket.subscriber = redis.createClient(redis_port, redis_host);
=======
 		socket.subscriber = redis.createClient(6379, 'redis.yasukawa.org');
>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47
		socket.subscriber.subscribe(userId);
		socket.subscriber.on("message", function(channel, message) {
			socket.emit('feed', JSON.parse(message));
		});
<<<<<<< HEAD
=======
		var req = http.createClient(3000, 'localhost').request('POST', '/subscription/' + userId);
		req.end();
>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47
	});
	socket.on('setPubStreamRate', function (args){
		console.log('setting pubstream rate for user' + args.user);
		if(args.user === undefined || args.rate === undefined){
			console.error("Invalid args: " + args);
			return;
		}
		var client = publicStreamClients[args.user];
		if(client == null){
			client = new TwitterStreamClient(args.user, pubStreamUrl);
			publicStreamClients[args.user] = client;
		}
		client.setRate.call(client, args.rate);
	});
	socket.on('disconnect', function(){
		if(socket.subscriber != null){
			socket.subscriber.unsubscribe();
			socket.subscriber.end();
		}
		
	});
});


<<<<<<< HEAD
var TwitterStreamClient = function(user, url){
	this.user = user;
	this.url = url;
	this.access_token = config.twitter_access_token;
	this.access_token_secret = config.twitter_access_token_secret;
=======
app.get('/subscription/:user', function(req, res){
	if(typeof(twitterStreamClients[req.params.user]) != 'undefined'){
		res.send(req.params.user);
	} else {
		res.send(404, "No subscription");
	}
});

app.post('/subscription/:user', function(req, res){
	var user = req.params.user;
	if(twitterStreamClients[user]){
		console.log("Twitter stream client for " + user + " already running");
		res.send(204);
		return;
	}
	console.log("Starting Twitter stream client for " + user);
	twitterStreamClients[user] = new TwitterStreamClient(user, userStreamUrl);
	twitterStreamClients[user].start.call(twitterStreamClients[user]);
	res.send(201);
});

var TwitterStreamClient = function(user, url){
	this.user = user;
	this.url = url;
	this.access_token = null;
	this.access_token_secret = null;
>>>>>>> e25897c5ed4f315e3e4d4ace6d20cf65233c4d47
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
	this.streamHandler = oa.get(this.url, this.access_token, this.access_token_secret );

	this.streamHandler.addListener('response', function (response) {
		response.setEncoding('utf8');
		response.addListener('data', function (chunk) {
			try {
				var msg = JSON.parse(chunk);
				if(typeof(msg.friends) == 'undefined'){
					var feed = normalizeTwitterFeed(msg);
					if(feed.id){
						if(client.count++ % client.mod == 0){
							storeFeed(client.user, feed);
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
	dynamodb.getItem('soag_users', this.user, {AttributesToGet: ['twitter_token', 'twitter_token_secret']}, function(error, data, meta){
		console.log(data);
		console.log(meta);
		if(error == null){
			callback(data);
		} else {
			console.error(user + " does not has authenticated with Twittter");
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

function storeFeed(user, feed){
	var writes = {
    		"soag_user_feeds": [
      			{put : {id: user, time: feed.time, messageId: feed.id }}
    		],
    		"soag_feeds": [
      			{put : feed}
    		]
  	};

	dynamodb.batchWriteItem(writes, function(error, meta){
		if(error){
			console.error(error);
			feed['error'] = 1;
		} 
		notifyUpdate(user, feed);
		console.log(meta);
	});
}

function getTwitterToken(user, callback){
	dynamodb.getItem('soag_users', user, {AttributesToGet: ['twitter_token', 'twitter_token_secret']}, function(obj, data, meta){
		console.log(data);
		console.log(meta);
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
