function initializeChatPhaseOne() {
	var promise = getUser();
	$.when(promise).then(function() {
		updateLoadingBar(1, 3);
		initializeChatPhaseTwo();
	});
}

function initializeChatPhaseTwo() {
	var promise = loadLanguage(user.language);
	$.when(promise).then(function() {
		updateLoadingBar(2, 3);
		initializeChatPhaseThree();
	});
}

function initializeChatPhaseThree() {
	var promises = fetchNews();
	$.when.apply($, promises).then(function() {
		updateLoadingBar(3, 3);
		getRecentMessagesOnLogin();
		fetchNewsRegularly();
		reportActivity();
		resizeWindow();
		sendActivity();
		if(userArray[user.id]["status"] == 0){
			sendStatus(1);
		}
		isTyping();
		$('#splashscreen').hide();
	});
}

function generateUserBar(fullsize) {
	
	$('#users').html("");;
	var userHTML = "";
	
	for(var i in userArray) {
		
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
		
		var editImage = i == user.id ? ' userbox-my-image" onclick="manualUpload(\'userImage\')' : "";
		var editName = i == user.id ? ' class="editable" data-global-variable="userDisplayName"' : "";
		var editStatusMessage = i == user.id ? ' editable" data-global-variable="userStatusMessage' : "";
		var userStatus = '<span class="status-circle status-' + statusClass + '"></span>';
		if(fullsize)
			html = '<div class="userbox"><div class="userbox-image"><img class="img-rounded' + editImage + '" src="' + getUserImage(userArray[i].image) + '"></div><div id="userbox' + i + '" class="userbox-data"><div class="userbox-username">' + userStatus + '<span' + editName + '>' + userArray[i].display_name + '</span></div><div class="userbox-statusmessage' + editStatusMessage + '">' + userArray[i].status_message +'</div></div><br class="clear"></div></div>';
		else
			html = '<span class="status-circled-background status-' + statusClass + '">' + userArray[i].display_name + '</span> ';
		
		if (i == user.id)
			userHTML = html + userHTML;
		else
			userHTML += html;
		
	}

	$('#users').append(userHTML);
}


function generateTopBar(fullsize) {
	var topHTML = "";

	var menuItems = [["chat", "comment", "#", "changeTab('chat')"],
		["settings", "cog", "#", "changeTab('settings')"],
		["stats", "stats", "#", "changeTab('stats')"],
		["logout", "log-out", "index.php?logout=1", ""]];

	if(fullsize) {
		topHTML = '<form><div id="top-left">\
			<img id="chat-image" src="' + getChatImage(chatInformation.chatImage) + '" class="img-circle" onclick="manualUpload(\'chatImage\')">\
				<div id="top-header">\
					<div id="chat-name" class="editable" data-global-variable="chatInformationName">' + chatInformation.name + '</div>\
					<div id="chat-topic" class="editable" data-global-variable="chatInformationTopic">' + chatInformation.topic + '</div>\
				</div>\
			  </div>\
			  <div id="top-right"></form>';
		  
		  for(var i = 0; i < menuItems.length; i++) {
			  var className = " tab-button-" + menuItems[i][0];
			  var activeTab = menuItems[i][0] == activeTabButton ? " active-tab-button" : "";
			  topHTML += '<div class="top-link-wrapper' + className + activeTab + '" onclick="' + menuItems[i][3] + '"><div class="top-link">\
				<a href="' + menuItems[i][2] + '" tabindex="' + (i+1) + '">\
					<span class="top-link-a glyphicon glyphicon-' + menuItems[i][1] + ' top-glyph"></span>\
					 ' + language[menuItems[i][0]] + '\
				</a>\
			</div></div>';
		  }
		  
		  topHTML += '</div>';
	}
	else {
		menuHTML = "";
		
		for(var i = 0; i < menuItems.length; i++) {
			var className = " tab-button-" + menuItems[i][0];
			var activeTab = menuItems[i][0] == activeTabButton ? " active-tab-button" : "";
			menuHTML += '<a class="menu-link' + className + activeTab + '" href="' + menuItems[i][2] + '" onclick="' + menuItems[i][3] + '; toggleMenu()">\
					<span class="glyphicon glyphicon-' + menuItems[i][1] + ' menu-glyph"></span>\
					 ' + language[menuItems[i][0]] + '\
				</a>';
		}
		  
		topHTML = '<div id="top-left">\
				<div class="editable chat-small-title" data-global-variable="chatInformationName">' + chatInformation.name + '</div>' + 
				(chatInformation.topic != '' ? '<div id="chat-small-colon" class="chat-small-title">:</div><div class="editable chat-small-title" data-global-variable="chatInformationTopic">' + chatInformation.topic + '</div>' : "") +
			'</div>\
			<span id="chat-small-menu" onclick="toggleMenu()" class="glyphicon glyphicon-menu-hamburger top-glyph"></span>';
		$('#chat-menu').html(menuHTML);
	}

	$('#chat-top').html(topHTML);
	
}

//Begin initialization
initializeChatPhaseOne();

//Various setup
Notification.requestPermission();
$(document).ready(function() {
  $.ajaxSetup({ cache: false });
});
$('#messages, #sidebar, #write-message, .tab').mCustomScrollbar(customScrollbarOptions);