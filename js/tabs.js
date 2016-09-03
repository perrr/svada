function changeTab(tab) {
	$(".active-tab").removeClass("active-tab");
	$("#tab-" + tab).addClass("active-tab");
	$(".active-tab-button").removeClass("active-tab-button");
	$(".tab-button-" + tab).addClass("active-tab-button");
	activeTabButton = tab;
	resizeWindow()
	
	// Disable upload form when not in chat
	if(tab != "chat") {
		disableUploadForm();
		$("#search").hide();
	}
	else
		activateUploadForm();
}