function getUserArray() {
	return $.ajax({url: getFormattedDataURL(["action=getAllUsers"]), dataType: "json"}).done(function(json){
		//Copy the old userArray
		//Update the userArray
		for(var i = 0; i < Object.keys(json).length; i++) {
			var user = {};
			var id = parseInt(Object.keys(json)[i]);
			if(json[id].online==0){
				json[id].status = 0;
			}

			var keys = Object.keys(json[id]);
			for(var j = 0; j < keys.length; j++) {
	
				user[keys[j]] = json[id][keys[j]];
			}
			userArray[id+1] = user;
		}
	});
}

function getUser() {
	return $.ajax({url: getFormattedDataURL(["action=getUser"]), dataType: "json"}).done(function(json){
		user = json;
	});
}

function logOn() {
	return $.ajax({url: getFormattedDataURL(["action=logOn"]), dataType: "json"}).done(function(json){
	});
}

function getEmoticonArray() {
	return $.ajax({url: getFormattedDataURL(["action=getAllEmoticons"]), dataType: "json"}).done(function(json){
		for(var i = 0; i<json.length; i++) {
			var allShortcuts = json[i]["shortcut"].split(" ");
			for(var d = 0; d<allShortcuts.length; d++){
				emoticonArray[allShortcuts[d]]= {path:json[i]["path"], name:json[i]["name"], shortcut:allShortcuts[d]};
			}
		}
	});
}

function getImageArray() {
	return $.ajax({url: getFormattedDataURL(["action=getAllImages"]), dataType: "json"}).done(function(json){
		for(var i = 0; i < Object.keys(json).length; i++) {
			imgArray[json[i].id] = { path: json[i].path, name: json[i].name, type: json[i].mime_type };
		}
	});
}

function getChatInformation() {
	return $.ajax({url: getFormattedDataURL(["action=getChatInformation"]), dataType: "json"}).done(function(json){
		chatInformation = {topic:json[0]["topic"], chatImage:json[0]["image"], name:json[0]["name"]};
	});
}

function getRecentMessagesOnLogin() {
	var doneLoading = jQuery.Deferred();
	
	$.ajax({url: getFormattedDataURL(["action=getRecentMessages"]), dataType: "json"}).done(function(json){
		if (json.length == 0) {
			doneLoading.resolve();
			return;
		}
		lastReceivedId = json[json.length-1]['id'];
		addMessages(json, "bottom");
		
		var numberOfMessages = 0;
		function loadMessagesUntilScrollbar() {
			if (chatHasScrollbar() || numberOfMessages == messages.length) {
				doneLoading.resolve();
				return;
			}
			numberOfMessages = messages.length;
			
			var promise = getNextMessages();
			$.when(promise).then(function() {
				loadMessagesUntilScrollbar();
			});
		};
		if (messages.length > 0) {
			loadMessagesUntilScrollbar();
		}
		else
			doneLoading.resolve();
		
    });
	
	return doneLoading;
}

function getNextMessages() {
	var first = messageIdStringToInt($(".message-content").first().attr("id"));
	if (messages[first] != "undefined"){
		var lastTimestamp = messages[first]['timestamp'];
		return $.ajax({url: getFormattedDataURL(["action=getNextMessages", "lastTimestamp="+lastTimestamp]), dataType: "json"}).done(function(json){
			if (json.length > 0){
				addMessages(json.reverse(), "top");
				$("#messages").mCustomScrollbar("scrollTo", $("#message" + first).parent(), { scrollInertia: 0 });
				addDateLine(json[0],false);
			}
		});
	}
}

function getNewMessages() {
	return $.ajax({url: getFormattedDataURL(["action=getMessages", "lastReceivedId="+lastReceivedId]), dataType: "json"}).done(function(json){
		if (json.length > 0) {
			lastReceivedId = json[json.length-1]['id'];
			addMessages(json, "bottom");

			scrollToBottom("#messages");
			if(!isActive){
				alertNewMessages();
			}
		}
    });
}

function getMessage(id) {
	return $.ajax({url: getFormattedDataURL(["action=getMessage", "id="+id]), dataType: "json"}).done(function(json){
		addMessage(json, false);
	});	
}

function addMessages(messages, displayAt) {
	if (messages.length == 0)
		return
	var promise = addMessage(messages[0], displayAt);
	$.when(promise).then(function() {
		var remainingMessages = messages.slice(1);
		addMessages(remainingMessages, displayAt);
	});
}

function addMessage(message, displayAt) {
	var id = message.id;
	messages[id] = message;
	var promise = parseMessage(id);
	
	return $.when(promise).then(function() {
		if (displayAt == "top")
			displayMessageTop(message);
		else if (displayAt == "bottom")
			displayMessageBottom(message);
	});
}

function displayMessageTop(message){
	if (newAuthor(message,false)) {
		if (message["author"] ===0){
			var messageHTML = displaySystemMessage(message);
		}
		else{
			var messageHTML = displayMessage(message);
		}
		$("#message-container").prepend(messageHTML);
	}
}

function displayMessageBottom(message){
	line = addDateLine(message, true)
	//if new author than last or a dateline is added, add everything
	if (newAuthor(message,true) || line){
		if (message["author"] ===0){
			var messageHTML = displaySystemMessage(message);
		}
		else{
			var messageHTML = displayMessage(message);
		}
		$("#message-container").append(messageHTML);		
	}
}

function displayMessage(message) {
		return '<div class="message">\
			<div class="message-image">\
				<img class="img-rounded" src="' + getUserImage(userArray[message["author"]].image) + '">\
			</div>\
			<div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div>\
			<div class="message-text">\
				<div class="message-author">'+ userArray[message["author"]].display_name + '</div>\
				<pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
			</div>\
		</div>';	
}

function displaySystemMessage(message) {
	return '<div class="message">\
		<div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div>\
		<div class="message-text">\
			<pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
		</div>\
	</div>';	
}

function newAuthor(message, bottom=true){
	if (bottom) {
		if (messages.length>2 && typeof messages[messages.length-2] !== 'undefined'){
			if (message["author"]==messages[messages.length-2].author && message["timestamp"]-messages[messages.length-2].timestamp < 300){
				$('#message'+messages[messages.length-2].id).after('<pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
				');
				return false;
			}
			else {return true;}
		}
			
		else {
			return true;
		}
	}
	else{
		var first = messageIdStringToInt($(".message-content").first().attr("id"));
		if (message["author"]==messages[first].author && messages[first].timestamp - message["timestamp"] < 300){
				$('#message'+messages[first].id).before('<pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
				');
				return false;
			}
			else {return true;}
	}
		
}

function addDateLine(message, bottom=true){
	var dateDivider = '<div class="date-divider"><span>'+timestampToDate(message.timestamp) +'</span></div>';
	if (messages.length>2 && typeof messages[messages.length-2] !== 'undefined'){
		var dateDivider = '<div class="date-divider"><span>'+timestampToDate(message.timestamp) +'</span></div>';
		var thatDay = new Date((messages[messages.length-2].timestamp)*1000);
		var thisDay = new Date(message.timestamp*1000);
		var difference = (thisDay.getFullYear()-thatDay.getFullYear())*100 + (thisDay.getMonth()-thatDay.getMonth())*10 + thisDay.getDate()-thatDay.getDate();
		if (difference != 0){
			if (bottom){$("#message-container").append(dateDivider);}
			else{$("#message-container").prepend(dateDivider);}
			return true;
		}	
		else return false;
	}
	//case of first message
	else{
		if (bottom){$("#message-container").append(dateDivider);}
		else{$("#message-container").prepend(dateDivider);}
		return true;
	}
}
function setTopic(value){
	$.ajax({url: getFormattedDataURL(["action=setTopic", "topic="+htmlEncode(value)]), dataType: "json", success: function(result){
	}});
}

function shareFile(id){
	$.ajax({url: getFormattedDataURL(["action=shareUploadedFile", "fileId="+id]), dataType: "json", success: function(result){
	}});
}

function setChatName(value){
	$.ajax({url: getFormattedDataURL(["action=setChatName", "chatName="+htmlEncode(value)]), dataType: "json", success: function(result){
	}});
}


function postMessage(content, userId) {
	$.ajax({url: getFormattedDataURL(["action=postMessage", "content="+htmlEncode(content), "user="+userId, "timestamp="+getCurrentTimestamp()]), dataType: "json", success: function(result){
	}});
}

function sendIsTyping(isTyping) {
	$.ajax({url: getFormattedDataURL(["action=setIsTyping", "isTyping="+htmlEncode(isTyping)]), dataType: "json", success: function(result){
	}});
}

function sendStatus(myStatus) {
	$.ajax({url: getFormattedDataURL(["action=setStatus", "status="+myStatus]), dataType: "json", success: function(result){
	}});
}

function sendActivity() {
	$.ajax({url: getFormattedDataURL(["action=checkUserActivity"]), dataType: "json", success: function(result){
	}});
}

function performSearch(searchstring, caseSensitive, userId) {
	$.ajax({url: getFormattedDataURL(["action=searchMessages", "string="+searchstring, "caseSensitive="+caseSensitive, "userId="+userId]), dataType: "json", success: function(result){
		displaySearchResults(result);
	}});
}
function setPassword(newPassword, oldPassword, userId) {
	$.ajax({url: getFormattedDataURL(["action=setPassword", "user="+userId, "newPassword="+newPassword, "oldPassword="+oldPassword]), dataType: "json", success: function(result){
		if (Object.keys(json).length ==0){
			//Insert code herefor empty result(success)
		}
		else {
			//Insert code here for errormessage
		}
	}});
}

function loadLanguage(newlanguage) {
	return $.ajax({url: "lang/"+ newlanguage+".json", dataType: "json"}).done(function(result){
		language = result;
	});
}

function pingServer(){
	$.ajax({url: getFormattedDataURL(["action=pingServer"]), dataType: "json", success: function(result){
		if (result.running != true){
			lostConnection();
		}
	}});	
}

function setStatusMessage(newStatusMessage){
	$.ajax({url: getFormattedDataURL(["action=setStatusMessage", "statusMessage="+htmlEncode(newStatusMessage)]), dataType: "json", success: function(result){
	}});
}

function setDisplayName(newDisplayName){
	$.ajax({url: getFormattedDataURL(["action=setDisplayName", "displayName="+htmlEncode(newDisplayName)]), dataType: "json", success: function(result){
	}});
}

function editMessage(newMessage, messageId){
	$.ajax({url: getFormattedDataURL(["action=editMessage", "message="+messageId, "content="+htmlEncode(newMessage)]), dataType: "json", success: function(result){
	}});
}