function getLoggedInUserId() {
	return userArray[0]['id'];
}

function statusIdToText(id) {
   	if(id==0) {
   		return 'Offline';
   	}
   	else if(id==1) {
   		return 'Available';
   	}
   	else if(id==2) {
   		return 'Away';
   	}
   	else if(id==3){
   		return 'Occupied';
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
	var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	var time =  day + '. ' + months[month] + ' ' + year + ', ' + hour + ':' + min + ':' + sec;
	return time;
}

function getFormattedDataURL(parameters) {
	return "data.php?" + parameters.join("&");
}

function getEmoticonHTML(emoticon){
	var path = emoticon["path"];
	var name = emoticon["name"];
	var html = '<img class="message-smiley" src=res/images/emoticons/"'+path+'" title="'+name+'">' 
	return html;
}

function isUrl(string) {
	var expression = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
	var regex = new RegExp(expression);
	return string.match(regex);
 }

function parseMessage(message) {
	var newmessage = "";
	var shortcuts = Object.keys(emoticonArray);
	var allWords = message.split(" ");
	
	//Apply syntax highlighting if requested
	if (allWords[0].charAt(0)=="!"){
		var language = allWords[0].slice(1,-1)
		newmessage = '<span class="monospace">' + message.substr(2) + '</span>'
			
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
		if(newmesssage.charAt(0)=" "){
			newmessage = newmessage.substr(1);
	}
	
	//Return parsed message
	return newmessage;
}

function getWhoIsTypingAsText(users) {
	var nrPeopleTyping = users.length;
	var typingMessage = "";
	for (var i=0; i<nrPeopleTyping-1;i++){
		typingMessage += userArray[users[i]]["display_name"] + ", ";
	}
	//checks if the sentence need to add "and".
	if (nrPeopleTyping >1){
		typingMessage += "and ";
	}
	typingMessage += userArray[users[nrPeopleTyping-1]]["display_name"] + " is typing."
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
		if (i != getLoggedInUserId() && newUsers[i]["is_typing"]==1){
			changes[1].push(i);
		}
	}
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

function resizeWindow() {
	$('#messages').css({'height':($('#chat-bottom').height()/100*85)-1});
	$('#message-text-field').css({'height':($('#chat-bottom').height()/100*15)-1});	
}

function htmlEncode(html) {
	html = jQuery('<div />').text(html).html().replace(/(?:\r\n|\r|\n)/g, '<br />');
	return encodeURIComponent(html);
}