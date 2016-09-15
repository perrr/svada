function initializeChatPhaseOne() {
	loadLanguage(user.language);
}

function initializeChatPhaseTwo() {
	getRecentMessagesOnLogin();
	//fetchNews();
	reportActivity();
	resizeWindow();
	if(userArray[user.id]["status"]==0){
		sendStatus(1);
	}
	isTyping();
	pingServer();
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
		var userStatus = '<span class="status-circle status-' + statusClass + '"></span>';
		if(fullsize)
			userHTML += '<div class="userbox"><div class="userbox-image"><img class="img-rounded' + editImage + '" src="' + getUserImage(userArray[i].image) + '"></div><div id="userbox' + i + '" class="userbox-data"><div class="userbox-username">' + userStatus + userArray[i].display_name + '</div><div class="userbox-statusmessage">' + userArray[i].status_message +'</div></div><br class="clear"></div></div>';
		else
			userHTML += '<span class="status-circled-background status-' + statusClass + '">' + userArray[i].display_name + '</span> ';
		
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
				<div id="chat-small-title" class="editable" data-global-variable="chatInformationName">' + chatInformation.name + '</div>' + 
				(chatInformation.topic != '' ? ': <div id="chat-small-title" class="editable" data-global-variable="chatInformationTopic">' + chatInformation.topic + '</div>' : "") +
			'</div>\
			<span id="chat-small-menu" onclick="toggleMenu()" class="glyphicon glyphicon-menu-hamburger top-glyph"></span>';
		$('#chat-menu').html(menuHTML);
	}

	$('#chat-top').html(topHTML);
	
}

//Run functions
getUser();
getUserArray();
getEmoticonArray();
getImageArray();
getChatInformation();

//Various setup
Notification.requestPermission();
$.ajaxSetup({ cache: false });
$('#messages, #sidebar, #write-message, .tab').mCustomScrollbar(customScrollbarOptions);