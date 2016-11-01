function postData(action, data) {
	return $.post("data.php?action=" + action, data);
}

function getData(action) {
	return $.get("data.php?action=" + action);
}

function getUserArray() {
	return getData("getAllUsers").done(function(json){
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
	return getData("getUser").done(function(json){
		user = json;
	});
}

function logOn() {
	return getData("logOn");
}

function getEmoticonArray() {
	return getData("getAllEmoticons").done(function(json){
		for(var i = 0; i<json.length; i++) {
			var allShortcuts = json[i]["shortcut"].split(" ");
			for(var d = 0; d<allShortcuts.length; d++){
				emoticonArray[allShortcuts[d]]= {path:json[i]["path"], name:json[i]["name"], shortcut:allShortcuts[d]};
			}
		}
	});
}

function getImageArray() {
	return getData("getAllImages").done(function(json){
		for(var i = 0; i < Object.keys(json).length; i++) {
			imgArray[json[i].id] = { path: json[i].path, name: json[i].name, type: json[i].mime_type };
		}
	});
}

function getChatInformation() {
	return getData("getChatInformation").done(function(json){
		chatInformation = {topic:json[0]["topic"], chatImage:json[0]["image"], name:json[0]["name"]};
	});
}

function getRecentMessagesOnLogin() {
	var doneLoading = jQuery.Deferred();
	
	getData("getRecentMessages").done(function(json){
		if (json.length == 0) {
			doneLoading.resolve();
			return;
		}
		lastReceivedId = json[json.length-1]['id'];
		var promise = addMessages(json, "bottom");
		$.when(promise).then(function() {
			function loadMessagesUntilScrollbar() {
				if (chatHasScrollbar() || messages[1] != undefined) {
					doneLoading.resolve();
					return;
				}
				
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
		
    });
	
	return doneLoading;
}

function getNextMessages() {
	var getNextMessagesPromise = jQuery.Deferred();
	var first = messageIdStringToInt($(".message-content").first().attr("id"));
	if (messages[first] != "undefined"){
		var lastTimestamp = messages[first]['timestamp'];
		postData("getNextMessages", { lastTimestamp }).done(function(json){
			if (json.length > 0){
				var promise = addMessages(json.slice(0).reverse(), "top");
				$.when(promise).then(function() {
					$("#messages").mCustomScrollbar("scrollTo", $("#message" + first).parent(), { scrollInertia: 0 });
					addDateLine(json[0],false);
					getNextMessagesPromise.resolve();
				});
			}
		});
	}
	return getNextMessagesPromise;
}

function getNewMessages() {
	return postData("getMessages", { lastReceivedId }).done(function(json){
		if (json.length > 0) {
			lastReceivedId = json[json.length-1]['id'];
			var promise = addMessages(json, "bottom");

			$.when(promise).then(function() {
				scrollToBottom("#messages");
				if(!isActive){
					alertNewMessages();
				}
			});
		}
    });
}

function getMessage(id) {
	return postData("getMessage", { id }).done(function(json){
		addMessage(json, false);
	});	
}

function addMessages(messages, displayAt) {
	var addMessagesPromise = jQuery.Deferred();
	addMessagesRecursive(messages, displayAt, addMessagesPromise);
	return addMessagesPromise;
}

function addMessagesRecursive(messages, displayAt, addMessagesPromise) {
	if (messages.length == 0) {
		addMessagesPromise.resolve();
		return
	}
	var promise = addMessage(messages[0], displayAt);
	$.when(promise).then(function() {
		var remainingMessages = messages.slice(1);
		addMessagesRecursive(remainingMessages, displayAt, addMessagesPromise);
	});
}

function addMessage(message, displayAt) {
	var id = message.id;
	messages[id] = message;
	var promise = parseMessage(message);
	
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

function displayMessage(message, idPrefix="") {
		return '<div class="message">\
			<div class="message-image">\
				<img class="img-rounded" src="' + getUserImage(userArray[message["author"]].image) + '">\
			</div>\
			<div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div>\
			<div class="message-text">\
				<div class="message-author">'+ userArray[message["author"]].display_name + '</div>\
				<pre id="' + idPrefix + 'message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
			</div>\
		</div>';	
}

function displaySystemMessage(message, idPrefix="") {
	return '<div class="message">\
		<div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div>\
		<div class="message-text">\
			<pre id="' + idPrefix + 'message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
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
	if (typeof messages[messages.length-2] !== 'undefined'){
		var dateDivider = '<div class="date-divider"><span>'+timestampToDate(message.timestamp) +'</span></div>';
		var thatDay = new Date((messages[messages.length-1].timestamp)*1000);
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
function setTopic(topic){
	postData("setTopic", { topic });
}

function shareFile(fileId){
	postData("shareUploadedFile", { fileId });
}

function setChatName(chatName){
	postData("setChatName", { chatName });
}


function postMessage(content, user) {
	postData("postMessage", { content, user });
}

function sendIsTyping(isTyping) {
	postData("setIsTyping", { isTyping });
}

function sendStatus(status) {
	postData("setStatus", { status });
}

function sendActivity() {
	getData("checkUserActivity");
}

function performSearch(searchstring, caseSensitive, userId) {
	postData("searchMessages", { "string": searchstring, caseSensitive, userId }).done(function(json){
		displaySearchResults(result);
	});
}

function loadLanguage(newlanguage) {
	return $.ajax({url: "lang/"+ newlanguage+".json", dataType: "json"}).done(function(result){
		language = result;
	});
}

function pingServer(){
	getData("pingServer").done(function(result){
		if (result.running != true){
			lostConnection();
		}
	});
}

function setStatusMessage(statusMessage){
	postData("setStatusMessage", { statusMessage });
}

function setDisplayName(displayName){
	postData("setDisplayName", { displayName });
}

function editMessage(content, messageId){
	postData("editMessage", { "message": messageId, content });
}

function getEditedMessages(){
	getData("getRecentlyEditedMessages").done(function(result){
		for (var i=0; i<result.length; i++){
			$("#message"+result[i].message).html(result[i].content);
		} 
	});
}
