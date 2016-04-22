function initializeChatPhaseOne() {
	loadLanguage(userArray[getLoggedInUserId()]["language"]);
}

function initializeChatPhaseTwo() {
	fetchNews();
	reportActivity();
	resizeWindow();
	if(userArray[getLoggedInUserId()]["status"]==0){
		sendStatus(1);
	}
	isTyping();
}

function generateUserBar(fullsize) {
	
	$('#sidebar').html("");
	var userHTML = "";
	
	for(var i in userArray) {
		if(i == 0) continue;
		
		var statusClass;
		if(userArray[i].status == 0) {
			statusClass = 'offline';
		}
		else if(userArray[i].status == 1) {
			statusClass = 'available';
		}
		else if(userArray[i].status == 2) {
			statusClass = 'away';
		}
		else if(userArray[i].status == 3){
			statusClass = 'occupied';
		}
		
		var userStatus = '<span class="status-circle status-' + statusClass + '">&nbsp;</span>';
		if(fullsize)
			userHTML += '<div class="userbox"><div class="userbox-image"><img class="img-rounded" src="res/images/uploads/'+ imgArray[userArray[i].image] + '"></div><div id="userbox' + i + '" class="userbox-data"><div class="userbox-username">' + userStatus + ' ' + userArray[i].display_name + '</div><div class="userbox-statusmessage">' + userArray[i].status_message +'</div></div><br class="clear"></div></div>';
		else
			userHTML += '<span class="status-circled-background status-' + statusClass + '">' + userArray[i].display_name + '</span> ';
		
	}
	
	if(!fullsize){
		userHTML = '<div class="centered-userbar">' + userHTML + '</div>'
	}

	$('#sidebar').append(userHTML);
}

//Run functions
getUserArray();
getEmoticonArray();
getImageArray();
getChatInformation();
Notification.requestPermission();