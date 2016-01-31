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
	var newmessage = ""
	var shortcuts = Object.keys(emoticonArray);
	var allWords = message.split(" ");
	var urlPattern = new RegExp("(http|ftp|https)://[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:/~+#-]*[\w@?^=%&amp;/~+#-])?")
	for (var word in allWords){
		//Make URL's clickable with HTML
		if (urlPattern.test(word)){
			newmessage = newmessage + " " + '<a href="' + url + '">' + url + '</a>';
		}
		//Replace emoticon shortcuts with HTML image
		else if (shortcuts.indexOf(word) != -1){
			newmessage = newmessage + " " + getEmoticonHTML(emoticonArray[word]);
		}
		else{
			newmessage = newmessage + " " + word
		}
	}
	
	//Return parsed message
	return newmessage;
}