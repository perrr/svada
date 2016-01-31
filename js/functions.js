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
	var a = new Date(timestamp);
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

function timestampToDateAndTime(timestamp, monthDayYear) {
	//Insert code here
}

function getFormattedDataURL(parameters) {
	return "data.php?" + parameters.join("&");
}

function getEmoticonHTML(emoticon){
	var path = emoticon[0];
	var name = emoticon[1];
	var html = '<img class="message-smiley" src="'+path+'" title="'+name+'">' 
	//alert(html)
	return html
}
//getEmoticonHTML({"haha.png","funny"});

function parseMessage(message) {
	//Replace emoticon shortcuts with HTML image
	//Insert code here
	var newmessage = ""
	var shortcuts = Object.keys(emoticonArray);
	var allWords = message.split(" ");
	for (var word in allWords){
		if (shortcuts.indexOf(word) != -1){
			newmessage = newmessage + " " + getEmoticonHTML(emoticonArray[word]);
		}
		else{
			newmessage = newmessage + " " + word
		}
	}
		
	
	
	//Make URL's clickable with HTML
	//Insert code here
	
	//Return parsed message
	return newmessage;
}