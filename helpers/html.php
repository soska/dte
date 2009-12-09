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
	function tag($selector = "div",$params='',$cr="\n",$comment=''){
		// initialize variables
		$element = $id = $classes = '';
		$tagMask = '<%1$s%4$s%2$s%3$s%5$s>'; // ugly, I know. Translation 1:element, 4: attributes, 2:id, 3:classes, 5:auto close tag
		$autoClose = '';
		// extract element type, id and classes from $selector
		preg_match("/([^.^#]*)?(#[^.]*)?(\..+)?/",$selector,$matches);
		

		// get rid of the selector
		array_shift($matches);
		// put matches into variables
		@ list($element,$id,$classes) = $matches;		
		// defaul $element to 'div'
		$element = (empty($element))?'div':$element;
		// if there's a closing slash in element name
		$slashPosition = strpos($element,"/");
		// if the slash is at the start,  we skip the attributes processing
		if ( $slashPosition=== 0) {
			$idclasses = "$id$classes";
			if (empty($comment) && !empty($idclasses)) {
				$comment = "/$idclasses";
			}

			$id = $classes = '';
		}else{
			// if the slash is at the end of the element, then set the autoclose variable
			if ($slashPosition==(strlen($element)-1) ) {
				$element = str_replace('/','',$element);
				$autoClose = '/';
			}
			$id = (!empty($id))?" id=\"".str_replace('#','',$id)."\"":$id;
			$classes = (!empty($classes))?" class=\"".trim(str_replace('.',' ',$classes))."\"":$classes;				
		}			

		$comment = (!empty($comment))?"<!-- $comment -->":$comment;
		$tag = sprintf($tagMask,$element,$id,$classes,$params,$autoClose);
		$tag = html_entity_decode($tag);
		return $cr.$tag.$comment.$cr;
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
	
	function entag($content,$selector = "div",$params='',$cr="\n",$comment=''){	
		$return = '';
		if (is_array($selector)) {
			$return = $content;
			$selectors = array_reverse($selector);
			foreach ($selectors as $selector) {
				$return  = $this->tag($selector,$params,$cr,$comment).$return.$this->tag("/$selector");
			}
		}else{
			$return  = $this->tag($selector,$params,$cr,$comment).$content.$this->tag("/$selector");			
		}
		return $return;
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
		$attributes = "";
		foreach ($atts as $att => $value) {
			if ($value === false) {
				continue;
			}elseif($value === true){
				$attributes .= " $att=\"$att\"";				
			}else{
				$attributes .= " $att=\"$value\"";				
			}
		}
		return $attributes;
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
	 * Outputs a DIV element
	 *
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */
	function div($options = null){

		if ($options) {
			if (!is_array($options)) {
				$options = array('id'=>$options);
			}
		}
		
		$options = set_merge(array('class'=>null,'id'=>null),$options);
		
		$class = ($options['class'])?"class='{$options['class']}'":'';
		$id = ($options['id'])?"id='{$options['id']}'":'';
		return "<div $class $id>\n";				
	}
	
	function divEnd($comment = null){
		$comment = ($comment)?"<!-- /$comment -->":'';
		return "</div>$comment\n";
	}
	
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