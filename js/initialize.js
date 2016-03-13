function initializeChat() {
	loadLanguage(userArray[getLoggedInUserId()]["language"]);
	fetchNews();
}

//Run functions
getUserArray();
getEmoticonArray();
getImageArray();
getChatInformation();
Notification.requestPermission();
resizeWindow();