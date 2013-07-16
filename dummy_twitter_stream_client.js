var tweetSource = "https://s3.amazonaws.com/self-paced-lab-05";
var zlib = require('zlib');
var sprintf = require('sprintf').sprintf;

var TwitterStreamClient = function(user, url, callback){
	this.user = user;
	this.url = url;
	this.callback = callback;
	this.init();
}

TwitterStreamClient.prototype.init = function(rate){
	this.chunkId = 1;
	this.mod = 1;
	this.count = 0;
	this.loading = false;

	this.tweets = [];
	this.running = false;
}

TwitterStreamClient.prototype.start = function(){
	this.running = true;
	var client = this;
	this.loadChunk(function(){
		this.emitTweet(this.tweets.shift());
	});
}

TwitterStreamClient.prototype.emitTweet = function(tweet){
	if(! this.running) return;
	console.log('Emitting Tweet');
	if(this.count++ % this.mod == 0){

		this.callback(this.user, normalizeTwitterFeed(tweet));
		this.count = 1;
	}
	if(this.tweets.length < 100){
		this.loadChunk(function(){
			this.emitTweet(this.tweets.shift());
		});
	}
	this.scheduleNext(tweet);
}

TwitterStreamClient.prototype.scheduleNext = function(tweet){
	var next = this.tweets.shift();
	if(next != null){
		var timeToNext = (next.tweetEpochDate - tweet.tweetEpochDate) * 1000 / 4;
		if(timeToNext <= 0){
			console.log("WARN: Time to next is 0 or negative: " + timeToNext);
			timeToNext = 5;	
		} 
		console.log("Scheduling next tweet " + timeToNext + " ms later");
		var client = this;
		setTimeout(function(){ client.emitTweet(next)}, timeToNext);
	} else {
		this.loadChunk(function(){
			this.emitTweet(this.tweets.shift());
		});
	}
}

TwitterStreamClient.prototype.stop = function(){
	console.log("Stopping twitter stream client");
	this.running = false;
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



TwitterStreamClient.prototype.loadChunk = function(onsuccess, onerror){
	if(this.loading){
		console.log("Already loading. Be patient");
		return;	
	} 
	console.log("Loading chunk");
	this.loading = true;
	var request = require('request');
	var client = this;

	var extension = ".gz";
	var url = sprintf("%s/%03d_tweets%s", tweetSource, this.chunkId, extension);

	console.log(url);
	var gunzip = zlib.createGunzip();
    var json = "";
 
    gunzip.on('data', function(data){
        json += data.toString();
    });
        
    gunzip.on('end', function(){
    	tweets = JSON.parse(json);			
    	tweets.sort(compareTweets);
        client.tweets = client.tweets.concat(tweets);
		client.chunkId++;
		client.loading = false;
		if(onsuccess)onsuccess.call(client);
    });

    gunzip.on('error', function(error){
		console.log("Failed to load chunk: " + error);			
		if(onerror)onerror.call(client);
    });
 
	var response = request(url);
	response.pipe(gunzip);
}

function compareTweets(t1, t2){
	return t1.tweetEpochDate - t2.tweetEpochDate;
}

function normalizeTwitterFeed(tweet){
	var normalized = {
		'id': tweet.tweetID,
		'time': parseInt(tweet.tweetEpochDate),
		'message': tweet.tweetText,
		'sns': 'twitter',
		'from': tweet.tweetFromUser
	}
	return normalized;
}


exports.TwitterStreamClient = TwitterStreamClient;
