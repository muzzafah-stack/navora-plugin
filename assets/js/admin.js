jQuery(document).ready(function($) {
	// Initialize Color Pickers
	$('.navora-color-picker').wpColorPicker();

	// Media Uploader
	var file_frame;
	$('#navora_upload_logo_btn').on('click', function(e) {
		e.preventDefault();

		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select or Upload Logo Image',
			button: {
				text: 'Use this image'
			},
			multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on('select', function() {
			var attachment = file_frame.state().get('selection').first().toJSON();
			$('#navora_logo_url').val(attachment.url);
			$('#navora_logo_id').val(attachment.id || '');
			$('#navora_logo_width').val(attachment.width || '');
			$('#navora_logo_height').val(attachment.height || '');

			var $previewImg = $('#navora-logo-preview img');
			$previewImg.attr('src', attachment.url);
			if (attachment.width) {
				$previewImg.attr('width', attachment.width);
			} else {
				$previewImg.removeAttr('width');
			}
			if (attachment.height) {
				$previewImg.attr('height', attachment.height);
			} else {
				$previewImg.removeAttr('height');
			}
			$('#navora-logo-preview').show();
		});

		// Finally, open the modal
		file_frame.open();
	});

	// Remove Logo
	$('#navora_remove_logo_btn').on('click', function(e) {
		e.preventDefault();
		$('#navora_logo_url').val('');
		$('#navora_logo_id').val('');
		$('#navora_logo_width').val('');
		$('#navora_logo_height').val('');
		$('#navora-logo-preview').hide();
		$('#navora-logo-preview img').attr('src', '').removeAttr('width').removeAttr('height');
	});

	// Toggle Secondary Bar Settings Visibility
	$('#navora_enable_topbar').on('change', function() {
		if ($(this).is(':checked')) {
			$('.navora-topbar-controls').slideDown(200);
		} else {
			$('.navora-topbar-controls').slideUp(200);
		}
	});
});

