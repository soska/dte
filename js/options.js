
(function($){	           
	$.fn.checkboxList = function(){
		var $box = $(this); 
		$('li:even',$box).css({'backgroundColor':"#f0f0f0"});
		var $checkboxes = $('input[type=checkbox]',$box);
		$('a.check-all',$box).click(function(e){
			e.preventDefault(); 
			$checkboxes.attr('checked',true);
		});
		$('a.check-none',$box).click(function(e){
			e.preventDefault();
			$checkboxes.attr('checked',false);
		});
	}
	/*
		Image Radio Custom Control
	*/
	$.fn.dupImageRadio = function() {
		var $radioGroup = $(this);
		var $field = $('input[type=hidden]',$radioGroup);
		var $images = $('img.radio',$radioGroup);
		var $none = $('.select-none',$radioGroup);
		$images.css({opacity:.6})
		.click(function(e){
			var $e = $(this);
			if (!$e.hasClass('selected')) {
				$images
					.removeClass('selected')
					.stop()
					.animate({opacity:.6},200);

				$field.get(0).value = $e.addClass('selected')
										.stop()
										.animate({opacity:1},100)
										.attr('title');				
			};
			e.stopPropagation();			
		})
		.hover(function(){
			$e = $(this);
			if (!$e.hasClass('selected')) {
				$e.stop().animate({opacity:1},100);				
			};
		},function(){
			$e = $(this);
			if (!$e.hasClass('selected')) {
				$e.stop().animate({opacity:.6},100);				
			};
		})
		.filter('.selected')
		.css({opacity:1});
				
		$none.click(function(e){
			$images
				.removeClass('selected')
				.stop()
				.animate({opacity:.5},200);
				$field.get(0).value = '';
			
			e.stopPropagation();					
		});
				
		return $radioGroup;
	};
})(jQuery)