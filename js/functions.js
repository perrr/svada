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
	//Insert code here
}