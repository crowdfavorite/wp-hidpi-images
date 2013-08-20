(function($){ 
	$(function() {
		$('img').each(function(index){
			var imageWidth = $(this).attr('width');
			var imageHeight = $(this).attr('height');
			if (!!imageWidth) {
				$(this).css('max-width', imageWidth + 'px');
			}
			if (!!imageHeight) {
				$(this).css('max-height', imageHeight + 'px');
			}
		});
	});
})(jQuery);
