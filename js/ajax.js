var emoticonArray = {};
var userArray = [];

function getUserArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllUsers"]), success: function(result){
		var json = JSON.parse(result);
		for(var i = 0; i < json.length; i++) {
			userArray[json[i]["id"]] = {username:json[i]["username"], displayName:json[i]["display_name"], status:json[i]["status"], image:json[i]["image"]};
		}
	}});
}

function getEmoticonArray() {
	$.ajax({url: getFormattedDataURL(["action=getAllEmoticons"]), success: function(result){
        var json = JSON.parse(result);
		for(var i = 0; i<json.length; i++) {
			var allShortcuts = json[i]["shortcut"].split(" ");
			for(var d = 0; d<allShortcuts.length; d++){
				emoticonArray[allShortcuts[d]]= {path:json[i]["path"], name:json[i]["name"]};
			}
		}
	}});
}

var lastReceivedId = 0;
var messages = [];

function getNewMessages() {
	$.ajax({url: getFormattedDataURL(["action=getMessages", "lastReceivedId="+lastReceivedId]), success: function(result){
		var json = JSON.parse(result);
		for(var i = 0; i < json.length; i++) {
			var id = json[i]['id'];
			
			//Update lastReceivedId if necessary
			if (id > lastReceivedId)
				lastReceivedId = json[i]['id'];
			
			//If the message is previously unrecieved, add it to array and display it
			if (!(id in messages)) {
				messages[id] = json[i];
				displayMessage(json[i]);
			}
		}
    }});
}

function fetchNews() {
	window.setInterval(function(){
		getNewMessages();
	}, 1000);
}

fetchNews();
getUserArray();

function displayMessage(message) {
	var messageHTML = '<div class="message"><span class="message-author">'+ userArray[message["author"]].displayName + '</span><span class="message-content">'+ message["content"] + '</span><span class="message-timestamp">' + timestampToTimeOfDay(message["timestamp"]) + '</div>';
	$("#messages").append(messageHTML);
}

function postMessage(content, userId) {
	$.ajax({url: getFormattedDataURL(["action=postMessage", "content="+content, "user="+userId, "timestamp="+getCurrentTimestamp()]), success: function(result){
	}});
}

function setPassword(newPassword, oldPassword, userId) {
	$.ajax({url: getFormattedDataURL(["action=setPassword", "user="+userId, "newPassword="+newPassword, "oldPassword="+oldPassword]), success: function(result){
		if (Object.keys(json).length ==0){
			continue;
			//Insert code herefor empty result(success)
		}
		else {
			continue;
			//Insert code here for errormessage
		}
	}});
}