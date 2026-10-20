(function ($) {
	'use strict';

	function bindColorPickers() {
		if ($.fn.wpColorPicker) {
			$('.oli-sl-color').wpColorPicker({
				change: function () {
					updatePreview();
				},
				clear: function () {
					updatePreview();
				}
			});
		}
	}

	function updatePreview() {
		var preview = document.getElementById('oli-sl-admin-preview');
		if (!preview) {
			return;
		}
		var val = function (name, fallback) {
			var input = document.querySelector('[name="oli_sl_options[' + name + ']"]');
			return input && input.value ? input.value : fallback;
		};
		preview.style.setProperty('--oli-sl-primary', val('primary_color', '#0073aa'));
		preview.style.setProperty('--oli-sl-button', val('button_color', '#0073aa'));
		preview.style.setProperty('--oli-sl-button-text', val('button_text_color', '#ffffff'));
		preview.style.setProperty('--oli-sl-text', val('text_color', '#333333'));
		preview.style.setProperty('--oli-sl-hover', val('hover_color', '#f0f0f0'));
	}

	$(function () {
		bindColorPickers();
		updatePreview();
		$(document).on('input change', '.oli-sl-color, [name^="oli_sl_options"]', updatePreview);

		$(document).on('click', '.oli-sl-media-upload', function (e) {
			e.preventDefault();
			var target = document.getElementById($(this).data('target'));
			var frame = wp.media({
				title: 'Select icon',
				button: { text: 'Use icon' },
				multiple: false
			});
			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				if (target) {
					target.value = attachment.url;
				}
			});
			frame.open();
		});

		$('#oli-sl-geocode-btn').on('click', function () {
			var address = [
				$('#oli_sl_address').val(),
				$('#oli_sl_city').val(),
				$('#oli_sl_state').val(),
				$('#oli_sl_zip').val(),
				$('#oli_sl_country').val()
			].filter(Boolean).join(', ');
			var status = $('#oli-sl-geocode-status');
			status.text(window.oliSLAdmin.i18n.geocoding);
			$.post(window.oliSLAdmin.ajaxUrl, {
				action: 'oli_sl_geocode_address',
				nonce: window.oliSLAdmin.nonce,
				address: address
			}).done(function (res) {
				if (res.success) {
					$('#oli_sl_latitude').val(res.data.lat);
					$('#oli_sl_longitude').val(res.data.lng);
					status.text('');
				} else {
					status.text(res.data && res.data.message ? res.data.message : 'Error');
				}
			});
		});

		$('#oli-sl-detect-columns').on('click', function () {
			var status = $('#oli-sl-detect-status');
			status.text(window.oliSLAdmin.i18n.detecting);
			$.post(window.oliSLAdmin.ajaxUrl, {
				action: 'oli_sl_detect_sheet_columns',
				nonce: window.oliSLAdmin.nonce,
				url: $('#oli_sl_sheets_url').val()
			}).done(function (res) {
				if (!res.success) {
					status.text(res.data && res.data.message ? res.data.message : 'Error');
					return;
				}
				status.text('');
				var html = '<table class="widefat striped"><thead><tr><th>Store field</th><th>Sheet column</th></tr></thead><tbody>';
				(res.data.fields || []).forEach(function (field) {
					var selected = res.data.map && res.data.map[field] !== undefined ? res.data.map[field] : '';
					html += '<tr><td>' + field + '</td><td><select name="oli_sl_options[sheets_column_map][' + field + ']">';
					html += '<option value="">—</option>';
					(res.data.headers || []).forEach(function (header, index) {
						html += '<option value="' + index + '"' + (String(selected) === String(index) ? ' selected' : '') + '>' + index + ' — ' + header + '</option>';
					});
					html += '</select></td></tr>';
				});
				html += '</tbody></table>';
				$('#oli-sl-column-map').html(html);
			});
		});
	});
})(jQuery);
