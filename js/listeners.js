//Fetch news from database regularly
function fetchNews() {
	window.setInterval(function(){
		getNewMessages();
		getUserArray();
		getUser();
	}, 1000);
	
	window.setInterval(function(){
		getChatInformation();
	}, 60000);
}

//Regularly report activity
function reportActivity() {
	 window.setInterval(function(){
		sendActivity();
	}, 240000);
}

//Regularly listen to the message field to determine if the user is currently typing
function isTyping() {
	var message = $('#message-text-field').html();
	lastStatus = 0;
	function loop(){
	setTimeout(function () {
        if (message ==($('#message-text-field').html()) && lastStatus ==1){
			sendIsTyping(0);
			lastStatus=0;
		}
		else if (message != ($('#message-text-field').html())){
			message = $('#message-text-field').html();
			if (lastStatus ==0){
				sendIsTyping(1);
				lastStatus=1;
			}
		}
		
		if (true){
			loop();
		}
	//Can change to increase and decrease how often you check if you are typing
    }, 1635);
	}
	loop();
}

//Update chat based on changes in userArray
function propagateUserChanges(changes) {
	if (getWhoIsTypingAsText(changes[1]) != ($('#whoistyping').html())){
		$('#whoistyping').html(getWhoIsTypingAsText(changes[1]));
	}
	var aChange =false;
	if(user["status"]!=3){
		for (var i in changes[0]){
			if (Object.keys(changes[0][i]).length!=0){
				aChange =true;
			}
			if ("oldStatus" in changes[0][i]){
				if(i == user.id) continue;
				if (changes[0][i]["oldStatus"]==0) {
					if (user["mute_sounds"]==0){
						playSound("user.mp3");
					}
					browserNotification("","res/images/uploads/"+imgArray[userArray[i]["image"]],userArray[i]["display_name"]+" " +language["loggedon"]);
				}
			}
		}
		if (aChange==true){
			generateUserBar(isFullsize());
		}
	}
}

//Update chat information based on changes
function propagateChatInformationChanges(changes) {
	//Insert code here
}

//Store quote on copy
function listenForQuote(id){
	$("#message" + id).bind('copy', function() {
		currentQuote.content = window.getSelection().toString();
		currentQuote.id = messageIdStringToInt($(this).attr('id'));
	});
}

//Listen for paste in message field
$("#message-text-field").bind('paste', function(e) {
	e.stopPropagation();
	e.preventDefault();

	var clipboard = e.originalEvent.clipboardData.getData("text/plain");
	
	//Check if clopboard contains a quote
	if(clipboard == currentQuote.content){
		insertToMessageField('<div title="' + messages[currentQuote.id].parsedContent + '" class="quote" data-timestamp="1" contenteditable="false">' + currentQuote.content + '<div class="quote-signature"> &ndash; ' + userArray[
		messages[currentQuote.id].author].display_name + ' - ' + timestampToTextualDateAndTime(messages[currentQuote.id].timestamp) + '</div></div>');
	}
	//Insert clipboard as normal if not
	else{
		insertToMessageField(clipboard);
	}
	scrollToBottom("#message-text-field");
});

//Listen for input in the message field
var messageTextField = $('#message-text-field');
messageTextField.keydown(function(e) {
	//Post message if Enter is pressed
	if (e.keyCode === 13 && $.trim(messageTextField.html()) != "" && !e.shiftKey) {
		e.preventDefault();
		postMessage($.trim(messageTextField.html()), user.id);
		messageTextField.html("");
	}
	
	//Prevent newline when there's no content
	else if(e.keyCode === 13 && $.trim(messageTextField.html()) == "" && !e.shiftKey) {
		e.preventDefault();
	}
	
	//Remove quote on backspace
	else if(e.keyCode === 8) {
		// Fix backspace bug in FF
		// https://bugzilla.mozilla.org/show_bug.cgi?id=685445
		var selection = window.getSelection();
		if (!selection.isCollapsed || !selection.rangeCount) {
			return;
		}

		var curRange = selection.getRangeAt(selection.rangeCount - 1);
		if (curRange.commonAncestorContainer.nodeType == 3 && curRange.startOffset > 0) {
			// we are in child selection. The characters of the text node is being deleted
			return;
		}

		var range = document.createRange();
		if (selection.anchorNode != this) {
			// selection is in character mode. expand it to the whole editable field
			range.selectNodeContents(this);
			range.setEndBefore(selection.anchorNode);
		} else if (selection.anchorOffset > 0) {
			range.setEnd(this, selection.anchorOffset);
		} else {
			// reached the beginning of editable field
			return;
		}
		range.setStart(this, range.endOffset - 1);


		var previousNode = range.cloneContents().lastChild;
		if (previousNode && previousNode.contentEditable == 'false') {
			// this is some rich content, e.g. smile. We should help the user to delete it
			range.deleteContents();
			event.preventDefault();
		}
	}
	
	else if(e.keyCode === 20) {
		alert($("#message-text-field").getCursorPosition2());
	}
});

//Listen for activity in this tab
window.onfocus = function () { 
	isActive = true;
	titleAlert = false;
};

window.onblur = function () { 
	isActive = false;
};

//If window is resized, call resize function
$(window).resize(resizeWindow);