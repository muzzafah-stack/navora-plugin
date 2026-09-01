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
			$('#navora-logo-preview img').attr('src', attachment.url);
			$('#navora-logo-preview').show();
		});

		// Finally, open the modal
		file_frame.open();
	});

	// Remove Logo
	$('#navora_remove_logo_btn').on('click', function(e) {
		e.preventDefault();
		$('#navora_logo_url').val('');
		$('#navora-logo-preview').hide();
		$('#navora-logo-preview img').attr('src', '');
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

