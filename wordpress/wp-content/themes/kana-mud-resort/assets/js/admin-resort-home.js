/**
 * Resort Home options: hero image picker.
 */
(function ($) {
	'use strict';

	$(function () {
		var frame;
		var $input = $('#hero_background_ids');
		var $btn = $('#kmr-hero-select-images');

		if (!$input.length || !$btn.length) {
			return;
		}

		$btn.on('click', function (e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: kmrResortHome.i18n.heroTitle,
				button: { text: kmrResortHome.i18n.heroButton },
				multiple: true,
				library: { type: 'image' },
			});

			frame.on('select', function () {
				var ids = frame
					.state()
					.get('selection')
					.map(function (a) {
						return a.id;
					});
				$input.val(ids.join(', '));
			});

			frame.open();
		});
	});
})(jQuery);
