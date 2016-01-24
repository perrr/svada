var emoticonArray = {};


function getUserArray() {
	//Insert code here
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

function getNewMessages(lastReceivedId) {
	$.ajax({url: getFormattedDataURL(["action=getMessages", "lastReceivedId="+lastReceivedId]), success: function(result){
        var json = JSON.parse(result);
		for(var i = 0; i < json.length; i++) {
			displayMessage(json[i]);
		}
    }});
}

function fetchNews() {
	//Insert code here
}

function displayMessage(message) {
	var messageHTML = '<div class="message"><span class="message-author">'+ message["author"] + '</span><span class="message-content">'+ message["content"] + '</span><span class="message-timestamp">' + timestampToTimeOfDay(message["timestamp"]) + '</div>';
	$("#messages").append(messageHTML);
}

function postMessage(content, userId) {
	$.ajax({url: getFormattedDataURL(["action=postMessage", "content="+content, "user="+userId, "timestamp="+getCurrentTimestamp()]), success: function(result){
	}});
}

//function setPassword(newPassword, oldPassword, userId) {
	//$.ajax({url: getFormattedDataURL(["action=setPassword", "$userId"=userId, "$newPassword"=newPassword, "$oldPassword"=oldPassword]), success: function(result){
		//var json = JSON.parse(result)
		//Insert code here. json is either empty (success)/successmessage or has an errormessage
//}