function initializeChatPhaseOne() {
	loadLanguage(user.language);
}

function initializeChatPhaseTwo() {
	fetchNews();
	reportActivity();
	resizeWindow();
	if(userArray[user.id]["status"]==0){
		sendStatus(1);
	}
	isTyping();
}

function generateUserBar(fullsize) {
	
	$('#sidebar').html("");
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
		
		var userStatus = '<span class="status-circle status-' + statusClass + '"></span>';
		if(fullsize)
			userHTML += '<div class="userbox"><div class="userbox-image"><img class="img-rounded" src="res/images/uploads/'+ imgArray[userArray[i].image] + '"></div><div id="userbox' + i + '" class="userbox-data"><div class="userbox-username">' + userStatus + ' ' + userArray[i].display_name + '</div><div class="userbox-statusmessage">' + userArray[i].status_message +'</div></div><br class="clear"></div></div>';
		else
			userHTML += '<span class="status-circled-background status-' + statusClass + '">' + userArray[i].display_name + '</span> ';
		
	}

	$('#sidebar').append(userHTML);
}


function generateTopBar(fullsize) {
	var topHTML = "";
	
	var menuItems = [["settings", "cog", "chat.php"],
		["stats", "stats", "stats.php"],
		["logout", "log-out", "index.php?logout=1"]];
	
	if(fullsize) {
		topHTML = '<div id="top-left">\
			<img id="chat-image" src="res/images/uploads/' + imgArray[chatInformation.chatImage] + '" class="img-circle">\
				<div id="top-header">\
					<h1 id="chat-name">' + chatInformation.name + '</h1>\
					<h2 id="chat-topic">' + chatInformation.topic + '</h2>\
				</div>\
			  </div>\
			  <div id="top-right">';
		  
		  for(var i = 0; i < menuItems.length; i++) {
			  topHTML += '<div class="top-link-wrapper"><div class="top-link">\
				<a href="' + menuItems[i][2] + '">\
					<span class="glyphicon glyphicon-' + menuItems[i][1] + ' top-glyph"></span>\
					 ' + language[menuItems[i][0]] + '\
				</a>\
			</div></div>';
		  }
		  
		  topHTML += '</div>';
	}
	else {
		menuHTML = "";
		
		for(var i = 0; i < menuItems.length; i++) {
			menuHTML += '<a class=\\\'menu-link\\\' href=\\\'' + menuItems[i][2] + '\\\'>\
					<span class=\\\'glyphicon glyphicon-' + menuItems[i][1] + ' menu-glyph\\\'></span>\
					 ' + language[menuItems[i][0]] + '\
				</a>';
		}
		  
		topHTML = '<div id="top-left">\
			<h1 id="chat-small-title">' + chatInformation.name + (chatInformation.topic != "" ? ": " + chatInformation.topic : "") + '</h1>\
			</div>\
			<span id="chat-small-menu" onclick="toggleMenu(\'' + menuHTML + '\')" class="glyphicon glyphicon-menu-hamburger top-glyph"></span>';
	}

	$('#chat-top').html(topHTML);
}

//Run functions
getUser();
getUserArray();
getEmoticonArray();
getImageArray();
getChatInformation();
Notification.requestPermission();