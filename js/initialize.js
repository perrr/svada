function initializeChat() {
	loadLanguage(userArray[getLoggedInUserId()]["language"]);
	fetchNews();
}
initialized = {"getUserArray":false, "getEmoticonArray":false, "getImageArray":false, "getChatInformation":false};
//Run functions
	getUserArray();
	getEmoticonArray();
	getImageArray();
	getChatInformation();
	Notification.requestPermission();
	resizeWindow();