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
    if (e.keyCode === 13 && $.trim(messageTextField.val()) != "" && !e.shiftKey) {
        postMessage(messageTextField.val(), getLoggedInUserId());
		messageTextField.val("");
    }
});

//If window is resized, call resize function
$(window).resize(resizeWindow());

//Run functions
fetchNews();
getUserArray();
getEmoticonArray();
getImageArray();
resizeWindow();
//initHighlightingOnLoad();