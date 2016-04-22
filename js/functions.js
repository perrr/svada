function getLoggedInUserId() {
	return userArray[0]['id'];
}

function getLoggedInUser() {
	return userArray[0];
}

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
	return Date.now() / 1000;
}

function timestampToTimeOfDay(timestamp) {
	var a = new Date(timestamp*1000);
	var hour = a.getHours();
	if(hour<10){
		hour= '0'+hour;
	}
	var min = a.getMinutes();
	if(min<10){
		min= '0'+min;
	}
	var time =  hour + ':' + min;
	return time;
}

function timestampToPreciseTimeOfDay(timestamp) {
	var a = new Date(timestamp*1000);
	var sec = a.getSeconds();
	if(sec < 10) {
		sec='0' + sec;
	}
	var min = a.getMinutes();
	if(min < 10){
		min = '0' + min;
	}
	var hour = a.getHours();
	if(hour < 10){
		hour = '0' + hour;
	}
	var time =  hour + ':' + min + ':' + sec;
	return time;
}

function timestampToDateAndTime(timestamp) {
	var a = new Date(timestamp*1000);
	var sec = a.getSeconds();
	if(sec < 10) {
		sec='0' + sec;
	}
	var min = a.getMinutes();
	if(min < 10){
		min = '0' + min;
	}
	var hour = a.getHours();
	if(hour < 10){
		hour = '0' + hour;
	}
	var day = a.getDate();
	var month = a.getMonth();
	var year = a.getFullYear();
	var months = [language["january"], language["february"], language["march"], language["april"], language["may"], language["june"], language["july"], language["august"], language["september"], language["october"], language["november"], language["december"]];
	var time =  day + '. ' + months[month] + ' ' + year + ', ' + hour + ':' + min + ':' + sec;
	return time;
}

function timestampToTextualDateAndTime(timestamp) {
	var thatDay = new Date(timestamp*1000);
	var thisDay = getCurrentTimestamp()*1000;
	var difference = thisDay-thatDay;
	difference = difference/(1000*60*60*24)
	if (difference > 2 || difference <0){
		return timestampToDateAndTime(timestamp);
	}
	else{
		var sec = thatDay.getSeconds();
		if(sec < 10) {
			sec='0' + sec;
		}
		var min = thatDay.getMinutes();
		if(min < 10){
			min = '0' + min;
		}
		var hour = thatDay.getHours();
		if(hour < 10){
			hour = '0' + hour;
		}
		
		var beginning = "";
		if (difference >1){
			beginning = language["yesterday"];
		}
		else{
			beginning = language["today"];
		}
		var time = beginning + ", " + hour + ':' + min + ':' + sec;
		return time;
	}
			
}

function getFormattedDataURL(parameters) {
	return "data.php?" + parameters.join("&");
}

function getEmoticonHTML(emoticon){
	var path = emoticon["path"];
	var name = emoticon["name"];
	var html = '<img class="message-smiley" src=res/images/emoticons/'+path+' title="'+name+'">' 
	return html;
}

function isUrl(string) {
	var expression = /((?:https?\:\/\/|www\.)(?:[-a-z0-9]+\.)*[-a-z0-9]+.*)/i;
	var regex = new RegExp(expression);
	return string.match(regex);
}

function parseMessage(message) {
	var newmessage = "";
	var shortcuts = Object.keys(emoticonArray);
	var allWords = message.split(" ");

	//No parsing if sentence start with @@
	if(message.substring(0,2)=="@@"){
		newmessage = message.substr(2);
	}
	
	//Apply syntax highlighting if requested
	else if (message.substring(0,2)=="!!"){
		newmessage = '<code>' + hljs.highlightAuto(htmlDecode(message.substr(2).replace(/<br\s*[\/]?>/gi, "\n"))).value + '</code>';
	}
	//if no syntax requested then check for links and emoticons
	else{
		for (var wordindex in allWords){
			var word = allWords[wordindex];
			//Make URL's clickable with HTML
			if (isUrl(word)){
				//Checks if the link do or do not start with http, https or ftp
				var pattern = /^((http|https|ftp):\/\/)/;
				if(!pattern.test(word)) {
					//if not then add // to href to not link locally
					newmessage = newmessage + " " + '<a href="//' + word + '" target="_blank">' + word + '</a>';
				}
				else{
					newmessage = newmessage + " " + '<a href="' + word + '" target="_blank">' + word + '</a>';
				}
				
			}
			//Replace emoticon shortcuts with HTML image
			else if (shortcuts.indexOf(word) != -1){
				newmessage = newmessage + " " + getEmoticonHTML(emoticonArray[word]);
			}
			else{
				newmessage = newmessage + " " + word;
			}
		}
		if(newmessage.charAt(0)==" "){
			newmessage = newmessage.substr(1);
		}
	}
	//Return parsed message
	return newmessage;
}

function getWhoIsTypingAsText(users) {
	var nrPeopleTyping = users.length;
	var typingMessage = "&nbsp;";
	if (nrPeopleTyping > 0){
		for (var i=0; i<nrPeopleTyping-1;i++){
			typingMessage += userArray[users[i]]["display_name"] + ", ";
		}
		//checks if the sentence need to add "and".
		if (nrPeopleTyping >1){
			typingMessage += language["and"]+ " ";
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
		if(i ==0){
			continue;
		}
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
		if (i != getLoggedInUserId() && newUsers[i]["is_typing"]==1){
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


function insertEmoticon(i){
	//Insert code here
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
		$('#sidebar').css({'width':'270px'});
		$('#mainbar').css({'width':$(window).width() - $('#sidebar').outerWidth()});
		$('#sidebar').css({'height':$(window).height() - $('#chat-top').height()});
		hideMenu();
		userbarOffset = 0;
	}
	else {
		generateUserBar(false);
		generateTopBar(false);
		$('#mainbar').css({'width':'100%'});
		$('#sidebar').css({'width':'100%'});
		$('#sidebar').css({'height':'100%'});
		$('#chat-top').css({'height':'45px'});
		userbarOffset = $('#sidebar').outerHeight();
		
	}
	$('#chat-bottom').css({'height':$(window).height() - $('#chat-top').height()});
	$('#messages').css({'height':$('#chat-bottom').height() - $('#whoistyping').height() - $('#message-text-field').outerHeight() - userbarOffset});	
}

function toggleMenu(items) {
	$('#chat-menu').html(items).slideToggle(500);
}

function hideMenu(){
	$('#chat-menu').hide();
}

function htmlEncode(html) {
	return encodeURIComponent(html);
}

function htmlDecode(html) {
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}

function scrollToBottom(id) {
	var container = $(id);
	container.scrollTop(container.prop("scrollHeight"));	
}

function setAsInitialized(functionName) {
	initialized[functionName] = true;
	var check = true;
	for(var i in initialized) {
		if (initialized.hasOwnProperty(i)){
			if (!initialized[i]){
				check=false;
				break;
			}
		}
	}
	if (check){
		initializeChatPhaseOne();
	}
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

function showTitleAlert(message) {
	titleAlerts =true;
	function loop(){
	setTimeout(function () {
        if (document.title == language["title"]){
			document.title = message;
		}
		else{
			document.title = language["title"];
		}
		if (titleAlerts) {
		loop()
		}
		else{
			document.title= language["title"];
		}
    }, 1200);
	}
	loop();
	document.title= language["title"];
}
function displaySearchResults(results) {
	//Insert code here
	alert(results);
}