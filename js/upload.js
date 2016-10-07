var $form = $('#uploadform');
var isBase64 = false;

var isAdvancedUpload = function() {
	var div = document.createElement('div');
	return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}();

function manualUpload(type) {
	if (type == "file") 
		$("#fileupload").attr('accept', '*/*')
	else
		$("#fileupload").attr('accept', 'image/*')
	$("#fileupload").data("type", type);
	$("#fileupload").trigger('click');
}

function submitUpload() {
	$form.submit();
}

function dataURLtoFile(dataurl, filename) {
    var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while(n--){
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new File([u8arr], filename, {type:mime});
}

function uploadFileFromBase64(base64) {
	var fileEnding = base64.split(";")[0].split("/")[1];
	var filename = "pasted" + getCurrentTimestamp() + "." + fileEnding;
	var file = dataURLtoFile(base64, filename);
	isBase64 = new FormData();
	isBase64.append($form.find('input[type="file"]').attr('name'), file);
	$form.submit();
}

function activateUploadForm() {

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
				$("#fileupload").data("type", "file");
				ajaxData = new FormData();
				$.each(droppedFiles, function(i, file) {
					$input = $form.find('input[type="file"]');
					ajaxData.append($input.attr('name'), file);
				});
			}
			else if(isBase64) {
				$("#fileupload").data("type", "file");
				ajaxData = isBase64;
				isBase64 = false;
			}
			else{
				ajaxData = new FormData($form.get(0))	
			}
			
			ajaxData.append("uploadType", $("#fileupload").data("type"));
			if($("#fileupload").data("type") == "file")
				ajaxData.append("share", 1);
			else
				ajaxData.append("share", 0);
			
			droppedFiles = false;
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
}

activateUploadForm()
