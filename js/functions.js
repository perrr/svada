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

function timestampToDateAndTime(timestamp) {
	var a = new Date(timestamp);
  var sec = a.getSeconds();
  if(sec<10){
   sec='0'+sec
  }
  var min = a.getMinutes();
  if(min<10){
    min= '0'+min;
  }
  var hour = a.getHours();
  if(hour<10){
    hour= '0'+hour;
  }
  var day = a.getDay();
  var month = a.getMonth();
  var year = a.getYear();
  var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  var time =  day+'. '+ months[month]+ ' ' +year+ ', '+ hour + ':' + min+ ':' +sec;
  return time;
}

function getFormattedDataURL(parameters) {
	return "data.php?" + parameters.join("&");
}

function getEmoticonHTML(emoticon){
	//Insert code here
}

function parseMessage(message) {
	//Replace emoticon shortcuts with HTML image
	//Insert code here
	
	//Make URL's clickable with HTML
	//Insert code here
	
	//Return parsed message
	return message;
}