(function($){	
	$.fn.dupSimpleImageChooser = function() {
		var $chooser = $(this);
		var id = $chooser.get(0).id;
		var $radiobox = $('.image-radio-group',this);
		var url = $('input[name=ajax_url]').get(0).value;
		var $button = $('input.refresh',this).click(function(e){
			$button = $(this).toggleClass('active');			
			if ($button.hasClass('active')) {
				$radiobox.html('loading…').load( url,{
							action:'simplemetabox_process',
							'cookie': encodeURIComponent(document.cookie),
							'id':id,	
							'postId':$('#post_ID').get(0).value
						}
					).show();				
			}else{
				$radiobox.hide();
			}
			e.stopPropagation();
		});
		
		$('body, a.thickbox').click(function(){
			if ($button.hasClass('active')) {
				$button.removeClass('active');
				$radiobox.hide();				
			};
		});
	};
	
})(jQuery)