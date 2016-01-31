function getLoggedInUserId() {
	//Insert code here
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
	//Insert code here
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
	var html = '<img class="message-smiley" src="'+path+'" title="'+name+'">' 
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
	for (var wordindex in allWords){
		var word = allWords[wordindex];
		//Make URL's clickable with HTML
		if (isUrl(word)){
			newmessage = newmessage + " " + '<a href="' + word + '" target="_blank">' + word + '</a>';
		}
		//Replace emoticon shortcuts with HTML image
		else if (shortcuts.indexOf(word) != -1){
			newmessage = newmessage + " " + getEmoticonHTML(emoticonArray[word]);
		}
		else{
			newmessage = newmessage + " " + word;
		}
	}
	
	//Return parsed message
	return newmessage;
}

function getWhoIsTypingAsText(users) {
	//Insert code here
}

function getUserChanges(oldUsers, newUsers) {
	//Insert code here
}

function getAllEmoticonsAsHtml() {
	//Insert code here
}

