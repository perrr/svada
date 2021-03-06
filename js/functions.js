function statusIdToText(id) {
	if(id==0) {
		return language['offline'];
	}
	else if(id==1) {
		return language['available'];
	}
	else if(id==2) {
		return language['away'];
	}
	else if(id==3){
		return language['occupied'];
	}
}

function getCurrentTimestamp(){
	return Math.round(Date.now() / 1000);
}

function timestampToTimeOfDay(timestamp) {
	return moment(timestamp*1000).format('LT');
}

function timestampToPreciseTimeOfDay(timestamp) {
	return moment(timestamp*1000).format('LTS');
}

function timestampToDate(timestamp){
	return moment(timestamp*1000).format('LL');
}

function timestampToDateAndTime(timestamp) {
	return moment(timestamp*1000).format('LLL');
}

function timestampToTextualDateAndTime(timestamp) {
	var thatDay = new Date(timestamp*1000);
	var thisDay = new Date();
	var difference = (thisDay.getFullYear()-thatDay.getFullYear())*100 + (thisDay.getMonth()-thatDay.getMonth())*10 + thisDay.getDate()-thatDay.getDate();
	if (difference >= 2 || difference <0){
		return timestampToDateAndTime(timestamp);
	}
	else{
		return moment(timestamp*1000).calendar();
	}
}
			

function getEmoticonHTML(emoticon, insert){
	var path = emoticon["path"];
	var name = emoticon["name"];
	var insertOnclick = insert ? ' onclick="insertEmoticon(\'' + emoticon.shortcut + '\')"' : "";
	var html = '<img class="message-emoticon" src=res/images/emoticons/'+path+' title="'+name+'"' + insertOnclick + '>';
	return html;
}

function isUrl(string) {
	var expression = /((?:https?\:\/\/|www\.)(?:[-a-z0-9]+\.)*[-a-z0-9]+.*)/i;
	var regex = new RegExp(expression);
	return string.match(regex);
}

function parseWord(word) {
	var shortcuts = Object.keys(emoticonArray);
	filePattern = /(.*)\{file\|(.*)\}(.*)/;
	langPattern = /(.*)\{lang\|(.*)\}(.*)/;
	//Make URL's clickable with HTML
	if (isUrl(word)){
		//Checks if the link do or do not start with http, https or ftp
		var pattern = /^((http|https|ftp):\/\/)/;
		if(!pattern.test(word)) {
			//if not then add // to href to not link locally
			return '<a href="//' + word + '" target="_blank">' + word + '</a>';
		}
		else{
			return '<a href="' + word + '" target="_blank">' + word + '</a>';
		}
		
	}
	//file parsing
	else if(filePattern.test(word)){
		var match = filePattern.exec(word);
		var id = parseInt(match[2]);
		if (imgArray[id].type.substr(0,6)=="image/"){
			return match[1] +  '<a href="download.php?id=' + id + '" target="_blank">' + imgArray[id].name + match[3] + '</a><a href="download.php?id=' + id + '" target="_blank"><img src="download.php?id=' + id + '" class="image-preview" /></a>'; 
		}
		else{
			return match[1] +  '<a href="download.php?id=' + id + '" target="_blank">' + imgArray[id].name + '</a>' + match[3];
		}
	}
	//Replace emoticon shortcuts with HTML image
	else if (shortcuts.indexOf(word) != -1){
		return getEmoticonHTML(emoticonArray[word]);
	}
	else if(langPattern.test(word)){
		var match = langPattern.exec(word);
		return match[1] + language[match[2]] + match[3];
	}
	else if(word.substr(0,10)=="{username|"){
		for (var aUser in userArray){
			if (userArray[aUser]["id"] ==parseInt(word.slice(10,-1))){
				return userArray[aUser]["display_name"];
			}
		}
	}
	else{
		return word;
	}
}

function parseMessage(originalMessage) {
	var message = originalMessage.content;
	var quotePromises = [];
	var mainPromise = jQuery.Deferred();
	
	var newmessage = "";
	
	//Parse quotes
	var content = $('<div>' + message + '</div>');
	content.children('.quote').each(function(){
		var quote = $(this);
		var id = quote.data('messageid');
		var promise = messages[id] === undefined ? getMessage(id) : $.when();
		quotePromises.push(promise);
		$.when(promise).then(function() {
			var html = '<div class="quote-content">' + quote.html() + '</div><div class="quote-signature">' + getQuoteSignature(id) + '</div>';
			quote.html(html);
			quote.attr('title', getQuoteTitle(id));
		});
	});

	$.when.apply($, quotePromises).then(function() {
		message = content.html();
		var allWords = message.split(" ");

		//No parsing if sentence start with @@
		if(message.substring(0,2)=="@@"){
			newmessage = message.substr(2);
		}
		
		//Apply syntax highlighting if requested
		else if (message.substring(0,2)=="!!"){
			newmessage = '<code>' + hljs.highlightAuto(htmlDecodeTextarea(message.substr(2).replace(/<br\s*[\/]?>/gi, "\n"))).value + '</code>';
		}
		//if no syntax requested then check for links and emoticons
		else{
			for (var wordindex in allWords){
				var word = allWords[wordindex];
				
				if (word.indexOf("<br>") !== -1) {
					var twoWords = word.split("<br>");
					newmessage = newmessage + " " + parseWord(twoWords[0]) + "<br>" + parseWord(twoWords[1]);
				}
				else {
					newmessage = newmessage + " " + parseWord(word);
				}
			}
			if(newmessage.charAt(0)==" "){
				newmessage = newmessage.substr(1);
			}
		}
		
		originalMessage.parsedContent = newmessage;
		mainPromise.resolve();
	});
	
	return mainPromise;
}

function getWhoIsTypingAsText(users) {
	var nrPeopleTyping = users.length;
	var typingMessage = "";
	if (nrPeopleTyping > 0){
		for (var i=0; i<nrPeopleTyping-1;i++){
			typingMessage += userArray[users[i]]["display_name"] + ", ";
		}
		//checks if the sentence need to add "and".
		if (nrPeopleTyping >1){
			typingMessage = typingMessage.substr(0,typingMessage.length-2);
			typingMessage += " " +language["and"]+ " ";
		}
		typingMessage += userArray[users[nrPeopleTyping-1]]["display_name"] + " "+ language["typing"];
	}
	return typingMessage;
}

function getUserChanges(oldUsers, newUsers) {
	var changes =[];
	changes[0] =[];
	changes[1] = [];
	//go over all users and check if something has changed.
	for (var i in oldUsers){
		changes[0][i]= [];
		if (oldUsers[i]["status_message"]!=newUsers[i]["status_message"]){
			changes[0][i]["newStatusMessage"] =newUsers[i]["status_message"];
		}
		if (oldUsers[i]["image"]!=newUsers[i]["image"]){
			changes[0][i]["newImage"] =newUsers[i]["image"];
		}
		if (oldUsers[i]["status"]!=newUsers[i]["status"]){
			changes[0][i]["oldStatus"] =oldUsers[i]["status"];
			changes[0][i]["newStatus"] =newUsers[i]["status"];
		}
		if (i != user.id && newUsers[i]["is_typing"]==1){
			changes[1].push(i);
		}
	}
	return changes;
}

function getChatInformationChanges(oldChatInformation, newChatInformation) {
	var changes = [];
	var changedName = oldChatInformation['name'] != newChatInformation['name'];
	var changedTopic = oldChatInformation['topic'] != newChatInformation['topic'];
	var changedImage = oldChatInformation['chatImage'] != newChatInformation['chatImage'];
	changes[0] = changedName;
	changes[1] = changedTopic;
	changes[2] = changedImage;
	return changes;
}


function insertEmoticon(emoticon){
	insertToMessageField(emoticon);
}

function getAllEmoticonsAsHtml() {
	var allEmoticonsHtml = ""
	var tempEmoticonHtml = ""
	var lastEmoteName= ""
	for (var i in emoticonArray){
		//check so that a emote with multiple shortcuts only show once
		if(emoticonArray[i]["name"]!=lastEmoteName){
			lastEmoteName=emoticonArray[i]["name"];
			tempEmoticonHtml = getEmoticonHTML(emoticonArray[i]);
			tempEmoticonHtml=tempEmoticonHtml.slice(0,-1);
			tempEmoticonHtml += ' onclick="insertEmoticon('+i+')">';
			allEmoticonsHtml+= tempEmoticonHtml;
		}
	}
	return allEmoticonsHtml;
}

function isFullsize() {
	return $(window).width() >= 800;
}

function resizeWindow() {
	//Redraw the sidebar with the correct size
	var userbarOffset;
	if(isFullsize()) {
		$('#chat-top').css({'height':'120px'});
		generateUserBar(true);
		generateTopBar(true);
		$('#sidebar').css({'width':'320px'});
		$('#mainbar').css({'width':$(window).width() - $('#sidebar').outerWidth()});
		$('#sidebar').css({'height':$(window).height() - $('#chat-top').outerHeight()});
		$('#users').css({'height':$('#sidebar').height()});
		hideMenu();
		userbarOffset = 0;
		var chatTitleId = '#chat-name';
		var chatTopicId = '#chat-topic';
		var topRightId = '#top-right';
	}
	else {
		generateUserBar(false);
		generateTopBar(false);
		$('#mainbar').css({'width':'100%'});
		$('#sidebar').css({'width':'100%'});
		$('#sidebar').css({'height':'100%'});
		$('#chat-top').css({'height':'45px'});
		userbarOffset = $('#sidebar').outerHeight();
		var chatTitleId = '.chat-small-title';
		var chatTopicId = '.chat-small-title';
		var topRightId = '#chat-small-menu';
	}
	$('#chat-bottom, #tabs > div').css({'height':$(window).height() - $('#chat-top').outerHeight()});
	$('#messages').css({'height':$('#chat-bottom').height() - $('#toolbar').height() - $('#write-message').outerHeight() - userbarOffset});
	
	//Adjust title size
	var fontSize = 35;
	while($('#top-left').outerWidth() + $(topRightId).outerWidth() > $('#chat-top').outerWidth() - 25) {
		var nameTopicDifference = fontSize >= 30 ? 10 : fontSize >= 25 ? 7.5 : fontSize >= 20 ? 5 : fontSize >= 10 ? 2.5 : 0;
		$(chatTitleId).css({'font-size': fontSize + 'px'});
		$(chatTopicId).css({'font-size': (fontSize-nameTopicDifference) + 'px'});
		fontSize -= 2.5;
		
	}
}

function toggleMenu() {
	$('#chat-menu').slideToggle(500);
}

function hideMenu(){
	$('#chat-menu').hide();
}

function htmlDecodeTextarea(html) {
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}

function htmlEncode(value){
  return $('<div/>').text(value).html();
}

function htmlDecode(value){
  return $('<div/>').html(value).text();
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function scrollToBottom(id) {
	$(id).mCustomScrollbar().mCustomScrollbar("scrollTo", "bottom", {scrollInertia: 0});
}

function updateLoadingBar(a, b) {
	var percentage = a/b*100;
	$('#loading-bar').css('width', percentage+'%').attr('aria-valuenow', percentage);    	
}

function playSound(sound) {
	var audio = new Audio("res/audio/"+sound);
	audio.play();
}

// Functions for notifications
//theBody: the text you want to notify the others with
//theIcon: the image of the person talking
//theTitle: title of the notification

function browserNotification(theBody, theIcon, theTitle) {
	var options = {
body: theBody,
icon: theIcon
	}
	var n = new Notification(theTitle, options);
	//how long the notification appears
	setTimeout(n.close.bind(n), 4000);
}
//an example
//spawnNotification("Hi everyone, welcome to the chat", "res/images/uploads/s.jpg", "Game Master");

function messageIdStringToInt(string){
	return parseInt(string.substring(7));
}

function insertToMessageField(content) {
	if (!$("#message-text-field").is(":focus")) {
		$("#message-text-field").focus();
	}
	
	var sel, range;
	if (window.getSelection) {
		// IE9 and non-IE
		sel = window.getSelection();
		if (sel.getRangeAt && sel.rangeCount) {
			range = sel.getRangeAt(0);
			range.deleteContents();

			// Range.createContextualFragment() would be useful here but is
			// non-standard and not supported in all browsers (IE9, for one)
			var el = document.createElement("div");
			el.innerHTML = content;
			var frag = document.createDocumentFragment(), node, lastNode;
			while ( (node = el.firstChild) ) {
				lastNode = frag.appendChild(node);
			}
			range.insertNode(frag);
			
			// Preserve the selection
			if (lastNode) {
				range = range.cloneRange();
				range.setStartAfter(lastNode);
				range.collapse(true);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	} else if (document.selection && document.selection.type != "Control") {
		// IE < 9
		document.selection.createRange().pasteHTML(content);
	}
}

(function( $ ){
	$.fn.placeCaretAtEnd = function() {
		var el = $(this).get(0);
		el.focus();
		if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
			var range = document.createRange();
			range.selectNodeContents(el);
			range.collapse(false);
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}
		else if (typeof document.body.createTextRange != "undefined") {
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(el);
			textRange.collapse(false);
			textRange.select();
		}
		return $(this);
	}; 
})( jQuery );

function showTitleAlert(message) {
	titleAlert = true;
	function loop(){
	setTimeout(function () {
        if (document.title == chatInformation.name){
			document.title = message;
		}
		else{
			document.title = chatInformation.name;
		}
		if (titleAlert) {
		loop()
		}
		else{
			document.title= chatInformation.name;
		}
    }, 1200);
	}
	loop();
	document.title= chatInformation.name;
}
function displaySearchResults(results) {
	//Insert code here
}

function alertNewMessages() {
	if(!titleAlert)
		showTitleAlert("New Activity!");
	if (user["mute_sounds"]==0){
		playSound("message.mp3");
	}
}

function errorNotification(content) {
	var $notification = $('<div class="notification notification-error">' + content + '</div>');
	$("#notifications" ).append($notification);
	$notification.slideDown("slow").delay(5000).slideUp("slow", function() { $(this).remove(); } );
}

function successNotification(content) {
	var $notification = $('<div class="notification notification-success">' + content + '</div>');
	$("#notifications" ).append($notification);
	$notification.slideDown("slow").delay(5000).slideUp("slow", function() { $(this).remove(); } );
}

function getUserImage(imageId) {
	return imageId != null ? 'uploads/' + imgArray[imageId].path : 'res/images/default/default_user_image.png';
}

function getChatImage(imageId) {
	return imageId != null ? 'uploads/' + imgArray[imageId].path : 'res/images/default/default_chat_image.png';
}

function handleDirectFieldEdit(field, value) {
	if (field == "chatInformationTopic"){
		if (value != chatInformation.topic){
			chatInformation.topic = value;
			setTopic(value);
			return true;
		}
	}
	else if (field == "chatInformationName"){
		if (value != null && value != ""){
			if (value != chatInformation.name){
				chatInformation.name = value;
				setChatName(value);
				return true;
			}
		}
	}
	else if (field == "userDisplayName"){
		if (value != null && value != ""){
			if (value != userArray[user.id].display_name){
				userArray[user.id].display_name = value;
				setDisplayName(value);
				return true;
			}
		}
	}
	else if (field == "userStatusMessage"){
		if (value != null){
			if (value != userArray[user.id].display_name){
				userArray[user.id].status_message = value;
				setStatusMessage(value);
				return true;
			}
		}
	}
	return false;
}

function lostConnection(){
	//what to do when there are no connection
}

function loadMoreMessages() {
	getNextMessages();
}

function setStatus(id) {
	sendStatus(id);
	userArray[user.id].status = id;
	generateUserBar(isFullsize());
}

function chatHasScrollbar() {
	return !$('#messages').hasClass('mCS_no_scrollbar');
}