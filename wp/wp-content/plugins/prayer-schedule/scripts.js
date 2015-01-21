(function($) {

	$(function() {
		try {
			if (typeof _wpps == 'undefined' || _wpps === null)
				_wpps = {};

			_wpps = $.extend({ cached: 0 }, _wpps);

			$('div.wpps > form').ajaxForm({
				beforeSubmit: function(formData, jqForm, options) {
					jqForm.wppsClearResponseOutput();
					jqForm.find('img.ajax-loader').css({ visibility: 'visible' });
					return true;
				},
				beforeSerialize: function(jqForm, options) {
					jqForm.find('.wpps-use-title-as-watermark.watermark').each(function(i, n) {
						$(n).val('');
					});
					return true;
				},
				data: { '_wpps_is_ajax_call': 1 },
				dataType: 'json',
				success: function(data) {
					var ro = $(data.into).find('div.wpps-response-output');
					$(data.into).wppsClearResponseOutput();

					if (data.invalids) {
						$.each(data.invalids, function(i, n) {
							$(data.into).find(n.into).wppsNotValidTip(n.message);
						});
						ro.addClass('wpps-validation-errors');
					}

					if (data.captcha)
						$(data.into).wppsRefillCaptcha(data.captcha);

					if (data.quiz)
						$(data.into).wppsRefillQuiz(data.quiz);

					if (1 == data.spam)
						ro.addClass('wpps-spam-blocked');

					if (1 == data.mailSent) {
						$(data.into).find('form').resetForm().clearForm();
						ro.addClass('wpps-mail-sent-ok');

						if (data.onSentOk)
							$.each(data.onSentOk, function(i, n) { eval(n) });
					} else {
						ro.addClass('wpps-mail-sent-ng');
					}

					if (data.onSubmit)
						$.each(data.onSubmit, function(i, n) { eval(n) });

					$(data.into).find('.wpps-use-title-as-watermark.watermark').each(function(i, n) {
						$(n).val($(n).attr('title'));
					});

					ro.append(data.message).slideDown('fast');
				}
			});

			$('div.wpps > form').each(function(i, n) {
				if (_wpps.cached)
					$(n).wppsOnloadRefill();

				$(n).wppsToggleSubmit();

				$(n).find('.wpps-acceptance').click(function() {
					$(n).wppsToggleSubmit();
				});

				$(n).find('.wpps-exclusive-checkbox').each(function(i, n) {
					$(n).find('input:checkbox').click(function() {
						$(n).find('input:checkbox').not(this).removeAttr('checked');
					});
				});

				$(n).find('.wpps-use-title-as-watermark').each(function(i, n) {
					var input = $(n);
					input.val(input.attr('title'));
					input.addClass('watermark');

					input.focus(function() {
						if ($(this).hasClass('watermark'))
							$(this).val('').removeClass('watermark');
					});

					input.blur(function() {
						if ('' == $(this).val())
							$(this).val($(this).attr('title')).addClass('watermark');
					});
				});
			});

		} catch (e) {
		}
	});

	$.fn.wppsToggleSubmit = function() {
		return this.each(function() {
			var form = $(this);
			if (this.tagName.toLowerCase() != 'form')
				form = $(this).find('form').first();

			if (form.hasClass('wpps-acceptance-as-validation'))
				return;

			var submit = form.find('input:submit');
			if (! submit.length) return;

			var acceptances = form.find('input:checkbox.wpps-acceptance');
			if (! acceptances.length) return;

			submit.removeAttr('disabled');
			acceptances.each(function(i, n) {
				n = $(n);
				if (n.hasClass('wpps-invert') && n.is(':checked')
				|| ! n.hasClass('wpps-invert') && ! n.is(':checked'))
					submit.attr('disabled', 'disabled');
			});
		});
	};

	$.fn.wppsNotValidTip = function(message) {
		return this.each(function() {
			var into = $(this);
			into.append('<span class="wpps-not-valid-tip">' + message + '</span>');
			$('span.wpps-not-valid-tip').mouseover(function() {
				$(this).fadeOut('fast');
			});
			into.find(':input').mouseover(function() {
				into.find('.wpps-not-valid-tip').not(':hidden').fadeOut('fast');
			});
			into.find(':input').focus(function() {
				into.find('.wpps-not-valid-tip').not(':hidden').fadeOut('fast');
			});
		});
	};

	$.fn.wppsOnloadRefill = function() {
		return this.each(function() {
			var url = $(this).attr('action');
			if (0 < url.indexOf('#'))
				url = url.substr(0, url.indexOf('#'));

			var id = $(this).find('input[name="_wpps"]').val();
			var unitTag = $(this).find('input[name="_wpps_unit_tag"]').val();

			$.getJSON(url,
				{ _wpps_is_ajax_call: 1, _wpps: id },
				function(data) {
					if (data && data.captcha)
						$('#' + unitTag).wppsRefillCaptcha(data.captcha);

					if (data && data.quiz)
						$('#' + unitTag).wppsRefillQuiz(data.quiz);
				}
			);
		});
	};

	$.fn.wppsRefillCaptcha = function(captcha) {
		return this.each(function() {
			var form = $(this);

			$.each(captcha, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find('img.wpps-captcha-' + i).attr('src', n);
				var match = /([0-9]+)\.(png|gif|jpeg)$/.exec(n);
				form.find('input:hidden[name="_wpps_captcha_challenge_' + i + '"]').attr('value', match[1]);
			});
		});
	};

	$.fn.wppsRefillQuiz = function(quiz) {
		return this.each(function() {
			var form = $(this);

			$.each(quiz, function(i, n) {
				form.find(':input[name="' + i + '"]').clearFields();
				form.find(':input[name="' + i + '"]').siblings('span.wpps-quiz-label').text(n[0]);
				form.find('input:hidden[name="_wpps_quiz_answer_' + i + '"]').attr('value', n[1]);
			});
		});
	};

	$.fn.wppsClearResponseOutput = function() {
		return this.each(function() {
			$(this).find('div.wpps-response-output').hide().empty().removeClass('wpps-mail-sent-ok wpps-mail-sent-ng wpps-validation-errors wpps-spam-blocked');
			$(this).find('span.wpps-not-valid-tip').remove();
			$(this).find('img.ajax-loader').css({ visibility: 'hidden' });
		});
	};

})(jQuery);