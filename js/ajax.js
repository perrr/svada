function getUserArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllUsers"]), dataType: "json", success: function(json){
		//Copy the old userArray
		var oldUserArray = jQuery.extend({}, userArray);
		//Update the userArray
		for(var i = 0; i < Object.keys(json).length; i++) {
			var user = {};
			var id = parseInt(Object.keys(json)[i]);

			var keys = Object.keys(json[id]);
			for(var j = 0; j < keys.length; j++) {
				user[keys[j]] = json[id][keys[j]];
			}
			userArray[id+1] = user;
		}

		
		//Report array as initialized
		if(!initialized.getUserArray)
			setAsInitialized("getUserArray");
		else {
			//Get changes and propagate those
			var changes = getUserChanges(oldUserArray, userArray);
			propagateUserChanges(changes);
		}
	}});
}

function getUser() {
	$.ajax({url: getFormattedDataURL(["action=getUser"]), dataType: "json", success: function(json){
		user = json;
		//Report variabel as initialized
		if(!initialized.getUser)
			setAsInitialized("getUser");
	}});
}

function getEmoticonArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllEmoticons"]), dataType: "json", success: function(json){
		for(var i = 0; i<json.length; i++) {
			break;
			var allShortcuts = json[i]["shortcut"].split(" ");
			for(var d = 0; d<allShortcuts.length; d++){
				emoticonArray[allShortcuts[d]]= {path:json[i]["path"], name:json[i]["name"]};
			}
		}
		
		//Report array as initialized
		if(!initialized.getEmoticonArray)
			setAsInitialized("getEmoticonArray");
	}});
}

function getImageArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllImages"]), dataType: "json", success: function(json){
		
		for(var i = 0; i < Object.keys(json).length; i++) {
			imgArray[json[i].id] = json[i].path;
		}
		
		//Report array as initialized
		if(!initialized.getImageArray)
			setAsInitialized("getImageArray");
	}});
}

function getChatInformation() {
	$.ajax({url: getFormattedDataURL(["action=getChatInformation"]), dataType: "json", success:function(json){
		//copy old chat information
		var oldChatInformation = jQuery.extend({}, chatInformation);
		//new chatInformation
		chatInformation = {topic:json[0]["topic"], chatImage:[0]["image"], name:json[0]["name"]};

		var changes = getChatInformationChanges(oldChatInformation, chatInformation);
		var somethingChanged = false;
		for(var i = 0; i < changes.length; i++) {
			if(changes[i]){
				somethingChanged = true;
				break;
			}
		}
		if(somethingChanged && initialized.getChatInformation){
			generateTopBar(isFullsize());
		}
		//Report array as initialized
		if(!initialized.getChatInformation)
			setAsInitialized("getChatInformation");
	}});
}

function getRecentMessagesOnLogin() {
	$.ajax({url: getFormattedDataURL(["action=getRecentMessages"]), dataType: "json", success: function(json){
		for(var i = 0; i < json.length; i++) {
			var id = json[i]['id'];
			lastReceivedId = json[i]['id'];
			messages[id] = json[i];
			messages[id].parsedContent = parseMessage(messages[id].content);
			displayMessage(json[i]);
		}
		scrollToBottom("#messages");
		fetchNews();
    }});
}

function getNewMessages() {
	$.ajax({url: getFormattedDataURL(["action=getMessages", "lastReceivedId="+lastReceivedId]), dataType: "json", success: function(json){
		var aChange = false;
		var initialLoading = messages.length == 0;
		for(var i = 0; i < json.length; i++) {
			var id = json[i]['id'];
			
			//Update lastReceivedId if necessary
			if (id > lastReceivedId)
				lastReceivedId = json[i]['id'];
			
			//If the message is previously unrecieved, add it to array and display it
			if (!(id in messages)) {
				aChange = true;
				messages[id] = json[i];
				messages[id].parsedContent = parseMessage(messages[id].content);
				displayMessage(json[i]);
			}
			scrollToBottom("#messages");
		}
		if(!isActive && aChange && !initialLoading){
			alertNewMessages();
		}
    }});
}

function displayMessage(message) {
	addDateLine(message);
	var messageHTML = '<div class="message">\
		<div class="message-image">\
			<img class="img-rounded" src="' + getUserImage(userArray[message["author"]].image) + '">\
		</div>\
		<div class="message-data">\
			<div class="message-author">'+ userArray[message["author"]].display_name + '</div>\
			<br>\
			<pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre>\
		</div>\
		<div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div>\
		<br class="clear">\
	</div>';
	$("#message-container").append(messageHTML);
	
	
}

function addDateLine(message){
	if (messages.length>2 && typeof messages[messages.length-2] !== 'undefined'){
		var thatDay = new Date((messages[messages.length-2].timestamp)*1000);
		var thisDay = new Date(message.timestamp*1000);
		var difference = (thisDay.getFullYear()-thatDay.getFullYear())*100 + (thisDay.getMonth()-thatDay.getMonth())*10 + thisDay.getDate()-thatDay.getDate();
		if (difference != 0){
			$("#message-container").append('<div class="date-divider">'+timestampToDate(message.timestamp) +'</div>');
		}	
	}
	//case of first message
	else{
		$("#message-container").append('<div class="date-divider">'+timestampToDate(message.timestamp) +'</div>');
	}
}
function setTopic(value){
	$.ajax({url: getFormattedDataURL(["action=setTopic", "topic="+htmlEncode(value)]), dataType: "json", success: function(result){
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
	$.ajax({url: "lang/"+ newlanguage+".json?a=v", dataType: "json", success: function(result){
		language = result;
		initializeChatPhaseTwo()
	}});
}

function pingServer(){
	$.ajax({url: getFormattedDataURL(["action=pingServer"]), dataType: "json", success: function(result){
		if (result.running != true){
			lostConnection();
		}
	}});	
}