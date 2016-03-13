//Fetch news from database regularly
function fetchNews() {
	window.setInterval(function(){
		getNewMessages();
	}, 1000);
}

//Listen for input in the message field
var messageTextField = $('#message-text-field');
messageTextField.keydown(function(e) {
	//Post message if Enter is pressed
    if (e.keyCode === 13 && $.trim(messageTextField.val()) != "" && !e.shiftKey) {
		e.preventDefault();
        postMessage(messageTextField.val(), getLoggedInUserId());
		messageTextField.val("");
    }
	//Prevent newline when there's no content
	else if(e.keyCode === 13 && $.trim(messageTextField.val()) == "" && !e.shiftKey) {
		e.preventDefault();
	}
});

//If window is resized, call resize function
$(window).resize(resizeWindow);