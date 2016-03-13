function initializeChat() {
	loadLanguage("english");
	fetchNews();
}
initialized = {"getUserArray":false, "getEmoticonArray":false, "getImageArray":false, "getChatInformation":false};
//Run functions
	getUserArray();
	getEmoticonArray();
	getImageArray();
	getChatInformation();
	resizeWindow();