<?php
/**
* 
*/
class HtmlHelper{
	
	/**
	 * Define Tag elements using CSS-ish selectors
	 *
	 * @param string $selector 
	 * @param string $comment 
	 * @return void
	 * @author Armando Sosa
	 */
	function tag($selector = "div",$params='',$format=null,$comment=''){
		return csml::tag($selector,$params,$format,$comment);
	}
	
	function tags($tags){
		$output = '';
		if (is_string($tags)) {
			$tags = explode(' ',$tags);
		}
		foreach ($tags as $tag) {
			$out.= $this->tag($tag);
		}
		return $out;
	}
	
	function entag($content,$selector = "div",$params='',$format=null,$comment=''){	
		return csml::entag($content,$selector,$params,$format,$comment);
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $src 
	 * @param string $alt 
	 * @param string $title 
	 * @return void
	 * @author Armando Sosa
	 */
	function img($src,$sel='',$alt=null,$title=null){
		if (!$title) {
			$title = $alt;
		}
		
		$atts = array('src'=>$src);
		if ($alt) $atts['alt'] = $alt;
		if ($title) $atts['title'] = $title;
		$atts = $this->attr($atts);
		return $this->tag('img/'.$sel,$atts);
	}
	
	/**
	 * Returns a properly formatted html attributes;
	 *
	 * @param string $atts 
	 * @return void
	 * @author Armando Sosa
	 */
	function attr($atts=array()){
		return csml::attr($atts);
	}           
	
	/**
	 * alias for attr
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function attributes($atts=array()){
		return $this->attr($atts);
	}

	/**
	 * outputs a link
	 *
	 * @param string $label 
	 * @param string $url 
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */
	function link($label,$url,$options = null){
		$options = set_merge(
			array(
				'class'=>null,
				'id'=>null,
				'title'=>null,
				'attributes'=>null
			),$options
		);
		$class = ($options['class'])?"class='{$options['class']}'":'';
		$id = ($options['id'])?"id='{$options['id']}'":'';
		$title = ($options['title'])?"title='{$options['title']}'":'';   
                   
		$attributes = '';
		if ($options['attributes']) {
			$attributes = $options['attributes'];
			if (is_array($attributes)) {
				$attibutes = $this->attributes($attributes);
			}
		}

		return "<a href='$url' $class $id $title $attributes>$label</a>";
	}
	
	function h($level = 1, $label = null){
		if (!$label) {
			$label = $level;
			$level = 1;
		}
		return $this->entag($label,"h$level");
	}
	
}

?>