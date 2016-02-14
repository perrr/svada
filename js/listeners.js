//Fetch news from database regularly
function fetchNews() {
	window.setInterval(function(){
		getNewMessages();
	}, 1000);
}

//Listen for input in the message field
var messageTextField = $('#message-text-field');
messageTextField.keyup(function(e) {
	//Post message if Enter is pressed
    if (e.keyCode === 13 && $.trim(messageTextField.val()) != "") {
        postMessage(messageTextField.val(), 1);
		messageTextField.val("");
    }
});

//Run functions
fetchNews();
getUserArray();
getEmoticonArray();
getImageArray();