function getUserArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllUsers"]), success: function(json){
		//Copy the old userArray
		var oldUserArray = jQuery.extend({}, userArray);
		//Update the userArray
		for(var i = 0; i < Object.keys(json).length; i++) {
			var user = {};
			var keys = Object.keys(json[i]);
			for(var j = 0; j < keys.length; j++) {
				user[keys[j]] = json[i][keys[j]];
			}
			userArray[i] = user;
		}
		//Get changes and propagate those
		var changes = getUserChanges(oldUserArray, userArray);
		propagateUserChanges(changes);
		
		
		//Report array as initialized
		if(!initialized.getUserArray)
			setAsInitialized("getUserArray");
	}});
}

function getUser() {
	//Insert code here
	$.ajax({url: getFormattedDataURL(["action=getUser"]), success: function(json){
		user = json;
	//Report variabel as initialized
	if(!initialized.getUser)
		setAsInitialized("getUser");
	}});
}

function getEmoticonArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllEmoticons"]), success: function(json){
		for(var i = 0; i<json.length; i++) {
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
	$.ajax({url: getFormattedDataURL(["action=getAllImages"]), success: function(json){
		
		for(var i = 0; i < Object.keys(json).length; i++) {
			imgArray[json[i].id] = json[i].path;
		}
		
		//Report array as initialized
		if(!initialized.getImageArray)
			setAsInitialized("getImageArray");
	}});
}

function getChatInformation() {
	$.ajax({url: getFormattedDataURL(["action=getChatInformation"]), success:function(json){
		//copy old chat information
		var oldChatInformation = jQuery.extend({}, chatInformation);
		//new chatInformation
		chatInformation = {topic:json[0]["topic"], chatImage:json[0]["image"], name:json[0]["name"]};
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

function getNewMessages() {
	$.ajax({url: getFormattedDataURL(["action=getMessages", "lastReceivedId="+lastReceivedId]), success: function(json){
		var aChange = false;
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
		}
		if(!isActive && aChange){
			alertNewMessages();
		}
    }});
}

function displayMessage(message) {
	var messageHTML = '<div class="message"><div class="message-image"><img class="img-rounded" src="res/images/uploads/'+ imgArray[userArray[message["author"]].image] + '"></div><div class="message-data"><div class="message-author">'+ userArray[message["author"]].display_name + '</div><div class="message-timestamp" title="' + timestampToDateAndTime(message["timestamp"]) + '">' + timestampToTimeOfDay(message["timestamp"]) + '</div><br class="clear"><pre id="message' + message.id + '" class="message-content">'+ message.parsedContent + '</pre></div><br class="clear"></div>';
	$("#messages").append(messageHTML);
	listenForQuote(message.id);
	scrollToBottom("#messages");
}

function postMessage(content, userId) {
	$.ajax({url: getFormattedDataURL(["action=postMessage", "content="+htmlEncode(content), "user="+userId, "timestamp="+getCurrentTimestamp()]), success: function(result){
	}});
}

function sendIsTyping(isTyping) {
	$.ajax({url: getFormattedDataURL(["action=setIsTyping", "isTyping="+htmlEncode(isTyping)]), success: function(result){
	}});
}

function sendStatus(myStatus) {
	$.ajax({url: getFormattedDataURL(["action=setStatus", "status="+myStatus]), success: function(result){
	}});
}

function sendActivity() {
	$.ajax({url: getFormattedDataURL(["action=checkUserActivity"]), success: function(result){
	}});
}

function performSearch(searchstring, caseSensitive, userId) {
	$.ajax({url: getFormattedDataURL(["action=searchMessages", "string="+searchstring, "caseSensitive="+caseSensitive, "userId="+userId]), success: function(result){
		displaySearchResults(result);
	}});
}
function setPassword(newPassword, oldPassword, userId) {
	$.ajax({url: getFormattedDataURL(["action=setPassword", "user="+userId, "newPassword="+newPassword, "oldPassword="+oldPassword]), success: function(result){
		if (Object.keys(json).length ==0){
			//Insert code herefor empty result(success)
		}
		else {
			//Insert code here for errormessage
		}
	}});
}

function loadLanguage(newlanguage) {
	$.ajax({url: "lang/"+ newlanguage+".json", success: function(result){
		language = result;
		initializeChatPhaseTwo()
	}});
}
