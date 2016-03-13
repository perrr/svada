//Fetch news from database regularly
function fetchNews() {
	window.setInterval(function(){
		getNewMessages();
		getUserArray();
	}, 1000);
}

//Update chat based on changes un userArray
function propagateUserChanges(changes) {
	$('#whoistyping').html(getWhoIsTypingAsText(changes[1]));
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

//Run functions
fetchNews();
getUserArray();
getEmoticonArray();
getImageArray();
getChatInformation();
resizeWindow();
loadLanguage("english");