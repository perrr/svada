function getUserArray() {
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
	//Insert code here	
}