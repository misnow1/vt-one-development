(function($) {

	$(function() {
		try {
			$.extend($.tgPanes, _wpps.tagGenerators);
			$('#taggenerator').tagGenerator(_wppsL10n.generateTag,
				{ dropdownIconUrl: _wpps.pluginUrl + '/images/dropdown.gif' });

			$('input#wpps-title:enabled').css({
				cursor: 'pointer'
			});

			$('input#wpps-title').mouseover(function() {
				$(this).not('.focus').css({
					'background-color': '#ffffdd'
				});
			});

			$('input#wpps-title').mouseout(function() {
				$(this).css({
					'background-color': '#fff'
				});
			});

			$('input#wpps-title').focus(function() {
				$(this).addClass('focus');
				$(this).css({
					cursor: 'text',
					color: '#333',
					border: '1px solid #777',
					font: 'normal 13px Verdana, Arial, Helvetica, sans-serif',
					'background-color': '#fff'
				});
			});

			$('input#wpps-title').blur(function() {
				$(this).removeClass('focus');
				$(this).css({
					cursor: 'pointer',
					color: '#555',
					border: 'none',
					font: 'bold 20px serif',
					'background-color': '#fff'
				});
			});

			$('input#wpps-title').change(function() {
				updateTag();
			});

			updateTag();

			if ($.support.objectAll) {
				if (! $('#wpps-mail-2-active').is(':checked'))
					$('#mail-2-fields').hide();

				$('#wpps-mail-2-active').click(function() {
					if ($('#mail-2-fields').is(':hidden')
					&& $('#wpps-mail-2-active').is(':checked')) {
						$('#mail-2-fields').slideDown('fast');
					} else if ($('#mail-2-fields').is(':visible')
					&& $('#wpps-mail-2-active').not(':checked')) {
						$('#mail-2-fields').slideUp('fast');
					}
				});
			}

			$('#message-fields-toggle-switch').text(_wppsL10n.show);
			$('#message-fields').hide();

			$('#message-fields-toggle-switch').click(function() {
				if ($('#message-fields').is(':hidden')) {
					$('#message-fields').slideDown('fast');
					$('#message-fields-toggle-switch').text(_wppsL10n.hide);
				} else {
					$('#message-fields').hide('fast');
					$('#message-fields-toggle-switch').text(_wppsL10n.show);
				}
			});

			if ('' == $.trim($('#wpps-additional-settings').text())) {
				$('#additional-settings-fields-toggle-switch').text(_wppsL10n.show);
				$('#additional-settings-fields').hide();
			} else {
				$('#additional-settings-fields-toggle-switch').text(_wppsL10n.hide);
				$('#additional-settings-fields').show();
			}

			$('#additional-settings-fields-toggle-switch').click(function() {
				if ($('#additional-settings-fields').is(':hidden')) {
					$('#additional-settings-fields').slideDown('fast');
					$('#additional-settings-fields-toggle-switch').text(_wppsL10n.hide);
				} else {
					$('#additional-settings-fields').hide('fast');
					$('#additional-settings-fields-toggle-switch').text(_wppsL10n.show);
				}
			});

		} catch (e) {
		}
	});

	function updateTag() {
		var title = $('input#wpps-title').val();

		if (title)
			title = title.replace(/["'\[\]]/g, '');

		$('input#wpps-title').val(title);
		var current = $('input#wpps-id').val();
		var tag = '[prayer-schedule ' + current + ' "' + title + '"]';

		$('input#prayer-schedule-anchor-text').val(tag);
	}

})(jQuery);