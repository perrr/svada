//Fetch news from database regularly
function fetchNews() {
	return [
		getUserArray(),
		getChatInformation(),
		getImageArray(),
		getEmoticonArray()
	];
}

function fetchNewsRegularly() {
	window.setInterval(function(){
		var oldUserArray = jQuery.extend({}, userArray);
		var oldChatInformation = jQuery.extend({}, chatInformation);
		
		var promises = fetchNews();
		$.when.apply($, promises).then(function() {
			getNewMessages();
			
			//Propagate changes
			var userChanges = getUserChanges(oldUserArray, userArray);
			var chatChanges = getChatInformationChanges(oldChatInformation, chatInformation);
			getEditedMessages();
			propagateUserArrayChanges(userChanges);
			propagateChatInformationChanges(chatChanges);
		});
	}, 1000);
}

//Regularly report activity
function reportActivity() {
	 window.setInterval(function(){
		sendActivity();
	}, 10000);
}

//Listen to various editable fields for clicks
$("body").on("click", ".editable", function(event) {
	var originalField = this;
	
	//If already in edit mode, do nothing
	if ($(this).find(">:first-child").is("input"))
		return;
	
	//Create and style the text field
	var textField = $('<input type="text" class="edit-value" value="' + $(this).html().replace(/[\""]/g, '&quot;') + '" />');
	textField.data("original-value", $(this).html());
	var padding = $(this).css('padding-top');
	textField.css({
		'width': $(this).outerWidth(),
		'padding': padding,
		'line-height': $(this).css('lineHeight')
	});
	
	$(this).css({
		'padding': 0
	});
	
	//Spawn the text field
	$(this).html(textField);

	//Set focus to end of field
	var temp = textField.focus().val();
	textField.val('').val(temp);
	
	textField.keydown(function(e){
		if(e.keyCode == 13){
			textField.endEdit(true);
		}
		else if(e.keyCode == 27){
			textField.endEdit(false);
		}
	});

	
});

$(document).mouseup(function(e) {
	//Toggle status container
    if (!$('.my-status-circle').is(e.target)) {
        $('.status-container').hide();
    }
	else {
		$('.status-container').toggle();
	}
	
	//Toggle emoticon menu
    if (!$('#emoticon-toggle').is(e.target) && !$('#emoticon-menu').is(e.target)) {
        $('#emoticon-menu').hide();
		$('#emoticon-toggle').removeClass('active-toolbar-item');
    }
	else if (!$('#emoticon-menu').is(e.target)) {
		$('#emoticon-menu').toggle();
		$('#emoticon-toggle').toggleClass('active-toolbar-item');
	}
	
	//Toggle editable fields
	$(".edit-value").each(function() {
		var textField = $(this);
		
		if($(e.target).closest('.editable').length && ($(e.target).find(">:first-child").get(0) === textField.get(0) || $(e.target).get(0) === textField.get(0))) {
			return;
		}

		textField.endEdit(true);
	});
});

(function( $ ){
	$.fn.endEdit = function(save) {
		var container = this.parent();
		doChange = false;
		
		if(save)
			doChange = handleDirectFieldEdit(container.data("global-variable"), escapeHtml(this.val()));
			
		$(container).css({
			'padding': this.css('padding-top')
		});
		
		if(doChange) {
			$(container).html(escapeHtml(this.val()));
			resizeWindow();
		}
		else {
			this.replaceWith(this.data("original-value"));
		}
		
		return this;
   }; 
})( jQuery );

//Regularly listen to the message field to determine if the user is currently typing
function isTyping() {
	var message = $('#message-text-field').html();
	function loop(){
	setTimeout(function () {
        message= checkTyping(message);
		
		if (true){
			loop();
		}
	//Can change to increase and decrease how often you check if you are typing
    }, 2635);
	}
	loop();
}

function checkTyping(message) {
        if (message ==($('#message-text-field').html())){
			sendIsTyping(0);
			lastStatus=0;
		}
		else if (message != ($('#message-text-field').html())){
			message = $('#message-text-field').html();
			if (lastStatus ==0){
				sendIsTyping(1);
				lastStatus=1;
			}
		}
		return $('#message-text-field').html();
}

//Update chat based on changes in userArray
function propagateUserArrayChanges(changes) {
	if (getWhoIsTypingAsText(changes[1]) != ($('#whoistyping').html())){
		$('#whoistyping').html(getWhoIsTypingAsText(changes[1]));
	}
	var aChange =false;
	for (var i in changes[0]){
		if (Object.keys(changes[0][i]).length!=0){
			aChange =true;
		}
		if ("oldStatus" in changes[0][i]){
			if(i == user.id) continue;
			if (changes[0][i]["oldStatus"]==0 && user["status"] != 3) {
				if (user["mute_sounds"]==0){
					playSound("user.mp3");
				}
				browserNotification("", getUserImage(userArray[i]["image"]) ,userArray[i]["display_name"]+" " +language["loggedon"]);
			}
		}
	}
	if (aChange==true){
		generateUserBar(isFullsize());
	}
}

//Update chat information based on changes
function propagateChatInformationChanges(changes) {
	if($.inArray(true, changes) !== -1){
		generateTopBar(isFullsize());
	}
}

//Store quote on copy
var blockNextCopy = false;
$(document).on( "copy", ".message-content, .quote-content", function() {
	if(blockNextCopy) {
		blockNextCopy = false;
		return;
	}
	
	var selection = window.getSelection().toString();
	var processedMessage = $(this).clone();
	
	if($(this).attr('class') == "message-content") {
		processedMessage.children('.quote').each(function() {
			$(this).remove();
		});
	}

	var decoded = decodeEntities(processedMessage.html());
	if(decoded.indexOf(selection.replace(/(?:\r\n|\r|\n)/g, '<br>')) == -1) {
		return;
	}
	
	if($(this).attr('class') == "message-content") {
		currentQuote.content = selection;
		currentQuote.id = messageIdStringToInt($(this).attr('id'));
	}
	else {
		currentQuote.content = selection;
		currentQuote.id = $(this).parent().data('messageid');
		blockNextCopy = true;
	}
});

quoteId = 0;

//Listen for paste in message field
$("#message-text-field").bind('paste', function(e) {
	var plaintext = e.originalEvent.clipboardData.getData("text/plain");
	if (plaintext) {
		e.stopPropagation();
		e.preventDefault();
		
		//Check if clipboard contains a quote
		if(plaintext.replace(/(?:\r\n|\r|\n)/g, '<br>') == currentQuote.content.replace(/(?:\r\n|\r|\n)/g, '<br>')){
			
			insertToMessageField('<div id="ind"></div>');
			var before = "";
			var after = "";
			var halfway = false;
			$('#message-text-field').contents().each(function () {
				if($(this).attr('id') == "ind") {
					halfway = true;
				}
				else if(!halfway && this.nodeType === 3) {
					before += $(this).text();
					$(this).remove();
				}
				else if(this.nodeType === 3) {
					after += $(this).text();
					$(this).remove();
				}
			});
				
			var content = getBeforeQuoteContainer(quoteId) + '<div title="' + getQuoteTitle(currentQuote.id) + '" class="quote unselectable" id="q' + quoteId + '" data-id="' + currentQuote.id + '" contenteditable="false"><div class="quote-content unselectable">' + escapeHtml(currentQuote.content).replace(/(?:\r\n|\r|\n)/g, '<br>') + '</div><div class="quote-signature unselectable">' + getQuoteSignature(currentQuote.id) + '</div></div>';
			
			if($('#ind').parent().attr("id") == "message-text-field") {
				$('#message-text-field').html(content);
			}
			else {
				var oldContent = $('#ind').parent().html().split('<div id="ind"></div>');
				before = oldContent[0];
				after = oldContent[1];
				$('#ind').parent().replaceWith(content);
			}
			
			var $after = $(getAfterQuoteContainer(quoteId));
			$("#q" + quoteId).after($after);
			$('#bq'+quoteId).html(before);
			$('#aq'+quoteId).html(after);
			$('#message-text-field').blur();
			$after.textFocus();
			$('#ind').remove();
			
			quoteId++;
		}
		//Insert clipboard as normal if not
		else{
			insertToMessageField(plaintext);
		}

		scrollToBottom("#write-message");
	}
	else {
		oldMessageContent = $("#message-text-field").html();
		if (is.not.firefox()) {
			var copiedData = e.originalEvent.clipboardData.items[0];
			var imageFile = copiedData.getAsFile();
			var reader = new FileReader();
			reader.onload = function (evt) {
				var result = evt.target.result;
				var img = document.createElement("img");
				img.src = result;
				$("#message-text-field").append(img);
			};
			reader.readAsDataURL(imageFile);
		}
		setTimeout(function(){
			newMessageContent = $("#message-text-field").html();
			if (oldMessageContent != newMessageContent) {
				var img = $("#message-text-field").children("img").first();
				var base64 = img.attr("src");
				var wrapper = $('<div class="pasted-image" style="display:inline"></div>')
				
				img.height($("#write-message").height() - 8);
				img.attr("contenteditable", "false").addClass('pasted-image');
				$("#message-text-field").html(wrapper.append(img));
				placeCaretAtEnd(wrapper.get(0), false);
				isSendingFile = true;
			}
		}, 10);
	}
});

(function($){
  $.event.special.destroyed = {
    remove: function(o) {
      if (o.handler) {
        o.handler()
      }
    }
  }
})(jQuery);

$(document).on('DOMNodeRemoved', ".pasted-image", function(e) {
    isSendingFile = false;
});

function getQuoteSignature(id) {
	 return '&ndash; ' + userArray[messages[id].author].display_name + ', ' + timestampToTextualDateAndTime(messages[id].timestamp)
}

function getQuoteTitle(id) {
	var title = "";
	
	$('<div>' + messages[id].parsedContent + '</div>').contents().each(function() {
		if($(this).attr('class') == "quote") {
			title += $(this).find('.quote-content').prepend(language['leftQuote']).append(language['rightQuote']).html();
		}
		else {
			title += $(this).text();
		}
		
		if(!title.endsWith('\n'))
			title += '\n';
	});
	
	return title.trim();
}

function getBeforeQuoteContainer(id){
	return '<div class="before-quote" id="bq' + id + '" tabindex="-1" contenteditable="true"></div>';
}

function getAfterQuoteContainer(id){
	return '<div class="after-quote" id="aq' + id + '" tabindex="-1" contenteditable="true"></div>';
}

function arrangeQuotes() {
	if ($('#message-text-field').html() == ""){
		if (editMessageId != -1){
			$("#message"+editMessageId).removeClass("editing-message");
		}
		editMessageId = -1;
	}
	$('#message-text-field').children('.quote').each(function () {
		var html = $(this).prop('outerHTML');
		if($('#message-text-field').html().indexOf("</div>" + html) == -1){
			$(this).before(getBeforeQuoteContainer($(this).attr('id').substring(1)));
		}
		
		if($('#message-text-field').html().indexOf(html + "<div") == -1){
			$(this).after(getAfterQuoteContainer($(this).attr('id').substring(1)));
		}
	});
	
	$('#message-text-field').children('.after-quote2, .before-quote2').each(function () {
		var id = $(this).attr('id').substring(2);
		if($("#q" + id).length == 0) {
			$(this).remove();
		}
	});
}

function decodeEntities(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}

(function( $ ){
   $.fn.textFocus = function() {
	   $('#message-text-field').attr('contenteditable','false');
	   $(this).focus();
	   $('#message-text-field').attr('contenteditable','true');
	   return this;
   }; 
})( jQuery );

(function( $ ){
   $.fn.removeQuote = function() {
		var quote = $(this);
		
		if(quote.prev().prev().attr('class') == "quote unselectable" || quote.next().next().attr('class') == "quote unselectable") {
			placeCaretAtEnd(quote.prev().get(0), true);
			quote.prev().append(quote.next().html());
			quote.next().remove();
			quote.remove();
		}
		else {
			var sep = '<span id="ind2"></span>';
			var s = quote.prev().html() + sep;
			quote.prev().remove();
			quote.next().replaceWith(quote.next().html());
			quote.remove();
			messageTextField.focus();
			insertToMessageField(s);
			var content = messageTextField.html().split(sep);
			messageTextField.html("");
			insertToMessageField(content[0]);
			placeCaretAtEnd(messageTextField.get(0), false);
			messageTextField.append(content[1]);
		}	
   }; 
})( jQuery );

function getCaretPosition(element) {
    var caretOffset = 0;
    if (typeof window.getSelection != "undefined") {
        var range = window.getSelection().getRangeAt(0);
        var preCaretRange = range.cloneRange();
        preCaretRange.selectNodeContents(element);
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        caretOffset = preCaretRange.toString().length;
    }
    else if (typeof document.selection != "undefined" && document.selection.type != "Control")
    {
        var textRange = document.selection.createRange();
        var preCaretTextRange = document.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        caretOffset = preCaretTextRange.text.length;
    }
    return caretOffset;
}

function placeCaretAtEnd(el, disableMessageField) {
	if(disableMessageField)
		$('#message-text-field').attr('contenteditable','false');
    el.focus();
    if (typeof window.getSelection != "undefined"
            && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.collapse(false);
        textRange.select();
    }
	if(disableMessageField)
		$('#message-text-field').attr('contenteditable','true');
}

function processMessage() {
	var processedMessage = "";
	
	$('#message-text-field').contents().each(function () {
		if(this.nodeType === 3) {
			processedMessage += $('#message-text-field').html().replace(/(<br>)*$/,"");
			return false;
		}
		else if($(this).attr('class') == "quote unselectable") {
			processedMessage += '<div class="quote" data-messageid="' + $(this).data('id') + '">' + $(this).find('.quote-content').html() + '</div>';
		}
		else{
			processedMessage += $(this).html();
		}
	});

	return processedMessage;
}

$('html').click(function() {
	$('.quote').removeClass('quote-selected');
});

$(document).on( "click", "#message-text-field .quote", function(e) {
	e.stopPropagation();
	$(':focus').blur();
	var sel = window.getSelection();
    sel.removeAllRanges();
	$('.quote').not(this).removeClass('quote-selected');
	$(this).addClass('quote-selected');
});

//Listen for input in the message field
var messageTextField = $('#message-text-field');

$(document).keydown(function(e) {
	if(e.keyCode === 46) {
		if($('.quote-selected').length > 0) {
			e.preventDefault();
			$('.quote-selected').removeQuote();
		}
	}
});

function getCurrentContainer() {
   var node = document.getSelection().anchorNode;
   return (node.nodeType == 3 ? node.parentNode : node);
}

function getEditSymbol(messageId, timestamp){
	return '<span class="glyphicon glyphicon-pencil" title="' + language['edited'] + ' ' + timestampToTimeOfDay(timestamp) + '"></span>';
}

function quitEditing() {
	$("#message"+editMessageId).removeClass("editing-message");
	editMessageId = -1
}

(function( $ ){
   $.fn.isQuote = function() {
	   return $(this).hasClass('quote');
   }; 
})( jQuery );

var isSendingFile = false;

messageTextField.keydown(function(e) {
	
	if($(':focus').attr('id') != "message-text-field"){
		messageTextField.focus();
	}
	checkTyping("umulig stuff hehehehehehheh");
	//Post message if Enter is pressed
	if (isSendingFile && e.keyCode !== 13 && e.keyCode !== 37 && e.keyCode !== 39 && e.keyCode !== 8) {
		e.preventDefault();
	}

	else if (e.keyCode === 13 && $.trim(messageTextField.html()) != "" && !e.shiftKey) {
		e.preventDefault();
		
		if (!isSendingFile) {
			if (editMessageId == -1){
				postMessage(processMessage(), user.id);
			}
			else {
				var editDate = Math.floor(Date.now() / 1000);
				editMessage(processMessage(),editMessageId, editDate);
				$("#message-edit-" + editMessageId).html(getEditSymbol(editMessageId, editDate));
				quitEditing();
			}
		}
		else {
			var img = $("#message-text-field").find("img").first().attr("src");
			uploadFileFromBase64(img);
			isSendingFile = false;
		}
		
		quoteId = 0;
		messageTextField.html("");
		checkTyping("");
	}
	
	//Move through quotes on left arrow
	else if(e.keyCode === 37) {

		var currentContainer = $(getCurrentContainer());
		if(getCaretPosition(currentContainer.get(0)) == 0) {
			if(currentContainer.prev().attr('class') == "quote unselectable") {
				e.preventDefault();
				var el = currentContainer.prev().prev().get(0);
				var range = document.createRange();
				range.setStartAfter( el );
				range.setEnd(el, el.childNodes.length);
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
				messageTextField.focus();
				el.scrollIntoView();
			}
		}
	}
	
	//Move through quotes on right arrow
	else if(e.keyCode === 39) {

		var currentContainer = $(getCurrentContainer());
		if(getCaretPosition(currentContainer.get(0)) == currentContainer.text().length) {
			if(currentContainer.next().attr('class') == "quote unselectable") {
				e.preventDefault();
				var el1 = currentContainer.next().get(0);
				var el = currentContainer.next().next().get(0);
				el.focus();
				var range = document.createRange();
				range.setStartAfter( el );
				range.setEnd(el, 0);
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
				el.focus();
				el.scrollIntoView();
			}
		}
	}
	
	//Remove quote on backspace
	else if(e.keyCode === 8) {
		
		var currentContainer = $(getCurrentContainer());
		if(getCaretPosition(currentContainer.get(0)) == 0) {
			if(currentContainer.prev().attr('class') == "quote unselectable") {
				e.preventDefault();
				currentContainer.prev().removeQuote();
			}
		}
	}
	

});

messageTextField.keyup(function(e) {

	//Edit message when pressing arrow up
	if (e.keyCode ===38){
		activateEditing();
	}
	
	//Quit editing if textfield is emptied
	if($.trim(messageTextField.text()) == "" && editMessageId != -1) {
		quitEditing();
	}
});

//Listen for shortcuts
document.onkeydown = function(e) {
	//IE fix
    e = e || event;
    var keyCode = (window.event) ? e.which : e.keyCode;
	
	//Toggle search field on ctrl + f 
    if (e.ctrlKey === true && keyCode === 70) {
        e.preventDefault();
		
		if(!$("#search").is(":visible") && activeTabButton == "chat") {
			$("#search").fadeIn(200);
			$("#search-field").focus();
		}
		else if(activeTabButton == "chat") {
			$("#search").fadeOut(200);
		}

        return false;
    }
}

//activating editing of a message
function activateEditing(){
	for (var i =messages.length-1; i>0; --i){
		if (messages[i].author == user.id){
			//check if it is more than 5 minutes since last message was sent
			if (Date.now()/1000 - messages[i].timestamp >300){
				break;
			}
			else{
				editMessageId=messages[i].id;
				messageTextField.html(messages[i].content);
				$("#message"+editMessageId).addClass("editing-message");
				messageTextField.placeCaretAtEnd();
				break;
			}
		}
	}
}

//Listen for activity in this tab
window.onfocus = function () { 
	isActive = true;
	titleAlert = false;
};

window.onblur = function () { 
	isActive = false;
};

//If window is resized, call resize function
$(window).resize(resizeWindow);