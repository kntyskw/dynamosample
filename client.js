$(init);

function init(){
	doWorkaroundForUISliders();
	
	// Gets 10 feeds since 5 minutes ago
	getFeeds(10, new Date().getTime() / 1000 - 300);
	
	var connection = new WSServerConnection(myUserId, myWsUrl);
	
	$('#pubStreamRatio').on("stop", function(event, ui){
		connection.setPublicStreamRate.call(connection, $(this).val());
	});
	
	$('#pubStreamRatio').on("change", function(event, ui){
			var rate = $(this).val();
			if(rate == 0 || rate == 100){
				connection.setPublicStreamRate.call(connection, $(this).val());
			}
	});
}

var WSServerConnection = function (userId, serverUrl){
	this.socket = io.connect(serverUrl);
	this.userId = userId;
	var connection = this;
	
	this.socket.on('who', function(message){
		console.debug("My user id is " + connection.userId);
		connection.socket.emit('i am', connection.userId);
	});

	this.socket.on('feed', function(feed){
		console.log(feed);
		addFeed(feed, $('#feeds'));
	});

	this.socket.on('disconnect', function(message){
		console.debug("Disconnected");
	});

};

WSServerConnection.prototype.setPublicStreamRate = function(rate){
	console.debug("Public Stream ratio is set to: " + rate / 100.0);
	this.socket.emit('setPubStreamRate', {user: this.userId, rate: rate / 100.0});
}

WSServerConnection.prototype.getPublicStreamRate = function(callback){
	this.socket.on('pubStreamRate', callback);
	this.socket.emit('getPubStreamRate');
}

function getFeeds(limit, since){
	$.get('feeds.php', {
		user: myUserId,
		limit: limit,
		since: since
	}, function(data){
		var feeds = JSON.parse(data);
		addFeeds(feeds, $('#feeds'));
	});
}

function addFeeds(feeds, list){
	for (var key in feeds){
		var feed = feeds[key];
		addFeed(feed, list);
	};
}

function addFeed(feed, list){
	var li = createFeed(feed);
	list.prepend(li);
	li.slideDown();
	if(list.children().length > 200){
		list.find('li:last').remove();
	}
}


function createFeed(feed){
	var li = $('<li class="ui-li ui-li-static ui-body-c ui-li-has-thumb" style="display:none" />');
	li.attr('id', feed.id);
	li.attr('time', feed.time);
	var time = $('<p class="ui-li-desc"/>');
	var date = new Date();
	date.setTime(feed.time * 1000);
	time.text(date.toLocaleString());
		
	var thumb = $('<img with="80" height="80" class="ui-li-thumb" />');
	thumb.attr('src', feed.thumb);
	
	var name = $('<h3 class="ui-li-heading" />');
	name.text(feed.from);
	
	var msg = $('<p class="ui-li-desc" />');
	msg.text(feed.message);
	li.append(time);
	li.append(thumb);
	li.append(name);
	li.append(msg);
	if(feed.error == 1){
		li.css({opacity: 0.2});
	}

	return li;
}


function doWorkaroundForUISliders(){
	// Adds start and stop event handlers for .ui-sliders.
	// (Unlike jQuery UI, jQuery mobile does not have an easy 
	// way to handle these events.
	// See:
	// http://stackoverflow.com/questions/4583083/jquerymobile-how-to-work-with-slider-events
	
	$(document).on({ 
	    "mousedown touchstart": function () {
	        $(this).siblings("input").trigger("start");
	    },
	    "mouseup touchend": function () {
	        $(this).siblings("input").trigger("stop");
	    }
	}, ".ui-slider");
}