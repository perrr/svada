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
	return Date.now();
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

function getFormattedDataURL(parameters) {
	return "data.php?" + parameters.join("&");
}