/* LicenseForge admin JS */
(function ($) {
	'use strict';

	$(function () {

		// Copy license key
		$(document).on('click', '.lf-copy', function (e) {
			e.preventDefault();
			var $pill = $(this).closest('.lf-key');
			var txt = ($pill.text() || '').replace('⎘', '').trim();
			if (navigator.clipboard) {
				navigator.clipboard.writeText(txt).catch(function(){});
			} else {
				var $tmp = $('<textarea>').val(txt).appendTo('body').select();
				document.execCommand('copy');
				$tmp.remove();
			}
			var $btn = $(this);
			var orig = $btn.text();
			$btn.text('✓');
			setTimeout(function () { $btn.text(orig); }, 1200);
		});

		// Tabs (data-lf-tab)
		$(document).on('click', '.lf-tab', function (e) {
			e.preventDefault();
			var $a = $(this);
			var target = $a.data('target');
			$a.closest('.lf-tabs-nav').find('.lf-tab').removeClass('nav-tab-active');
			$a.addClass('nav-tab-active');
			$('.lf-tab-content').hide();
			$('#' + target).show();
		});

		// Confirm before destructive submits
		$(document).on('submit', 'form.lf-confirm', function (e) {
			if (!confirm(LicenseForge.i18n.sure)) {
				e.preventDefault();
			}
		});

		// Toggle WP "select all" for our list tables
		$(document).on('change', '.lf-check-all', function () {
			$(this).closest('table').find('tbody input.lf-check').prop('checked', this.checked);
		});

	});
})(jQuery);
