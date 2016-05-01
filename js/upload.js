var isAdvancedUpload = function() {
	var div = document.createElement('div');
	return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}();

function manualUpload() {
	$("#fileupload").trigger('click');
}

function submitUpload() {
	$form.submit();
}

var $form = $('#uploadform');

if(isAdvancedUpload) {
	$form.addClass('has-advanced-upload');
	
	var droppedFiles = false;

	$form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
		e.preventDefault();
		e.stopPropagation();
	})
	.on('dragover dragenter', function() {
		$form.addClass('dragover');
	})
	.on('dragleave dragend drop', function() {
		$form.removeClass('dragover');
	})
	.on('drop', function(e) {
		droppedFiles = e.originalEvent.dataTransfer.files;
		$form.submit();
	});

}

$form.on('submit', function(e) {
	if($form.hasClass('is-uploading')) {
		errorNotification(language["alreadyUploading"]);
		return false;
	}
	$form.addClass('is-uploading');

	if(isAdvancedUpload) {
		e.preventDefault();

		var ajaxData;

		if(droppedFiles) {
			ajaxData = new FormData();
			$.each(droppedFiles, function(i, file) {
				$input = $form.find('input[type="file"]');
				ajaxData.append($input.attr('name'), file);
			});
		}
		else{
			ajaxData = new FormData($form.get(0))	
		}

		$.ajax({
			url: "data.php?action=upload",
			type: $form.attr('method'),
			data: ajaxData,
			cache: false,
			contentType: false,
			processData: false,
			complete: function() {
				$form.removeClass('is-uploading');
			},
			success: function(json) {
				if(json["status"] == "success"){
					successNotification(json['message']);
				}
				else{
					errorNotification(json['message']);
				}
			}
		});	
	}
	else {
		//Todo: Add support for older browsers
	}
});