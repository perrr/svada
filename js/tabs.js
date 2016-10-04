function changeTab(tab) {
	$(".active-tab").removeClass("active-tab");
	$("#tab-" + tab).addClass("active-tab");
	$(".active-tab-button").removeClass("active-tab-button");
	$(".tab-button-" + tab).addClass("active-tab-button");
	activeTabButton = tab;
	resizeWindow()
}