function statusIdToText(id) {
   //Insert code here
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
document.write( statusIsToText(2) )

function timestampToTimeOfDay(timestamp) {
	var a = new Date(UNIX_timestamp * 1000);
  var hour = a.getHours();
  if(hour<10){
  hour= '0'+hour;
  }
  var min = a.getMinutes();
  if(min<10){
  min= '0'+min;
  }
  var time =  hour + ':' + min;
  return time
}