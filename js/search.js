function doSearch() {
	var searchTerm = $("#search-field").val();
	changeTab('search', true);
	$("#search").hide();
	$("#search-header").html(language["searchresults"] + " " + language["leftQuote"] + searchTerm + language["rightQuote"]);
	displayResults(searchTerm, 0, 0);
}

function displayResults(string, caseSensitive, userId) {
	return postData("searchMessages", { string, caseSensitive, userId }).done(function(json){
		$("#search-num-results").html(json.length + " " + (json.length != 1 ? language["results"] : language["result"]) + ".");
		var html = "";
		var promises = [];
		var currentDate = new Date(0);
		for (var i = 0; i < json.length; i++) {
			var thisDate = new Date(json[i].timestamp*1000);
			if (getDayFromDate(currentDate) != getDayFromDate(thisDate)) {
				html += '<div class="date-divider"><span>' + timestampToDate(thisDate.getTime()/1000) + '</span></div>';
				currentDate = thisDate;
			}
			
			promises.push(parseMessage(json[i]));
			
			if (json[i].author > 0)
				html += displayMessage(json[i], "search-");
			else 
				html += displaySystemMessage(json[i], "search-");
			
		}
		$("#search-results").html(html);
		
		for (var i = 0; i < promises.length; i++) {
			
			$.when(promises[i]).then(function() {
				var re = new RegExp("("+string+")", "i");
				$("#search-message" + json[i].id).html(json[i].parsedContent.replace(re,'<b>$1</b>'));
			});
			
		}
	});
}

$("#search-field").on('keyup', function (e) {
    if (e.keyCode == 13) {
        doSearch();
    }
});

function getDayFromDate(date) {
	return date.getDate() + "-" + date.getMonth() + "-" + date.getFullYear();
}