function getUserArray() {
	//Insert code here
}

function getEmoticonArray() {
	//Insert code here
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
	//Insert code here
}

function setPassword(newPassword, oldPassword, userId) {
	//Insert code here
}