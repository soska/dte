<?php
/**
 * CSML - Useful class to generate HTML tags from CSS-like selectors.
 *
 * @author Armando Sosa
 * @version 0.1
 * @copyright Armando Sosa, 14 December, 2009
 **/

/*
	The MIT License

	Copyright 2009-2010, Armando Sosa.

	Permission is hereby granted, free of charge, to any person obtaining a
	copy of this software and associated documentation files (the "Software"),
	to deal in the Software without restriction, including without limitation
	the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the
	Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
	DEALINGS IN THE SOFTWARE.
*/

/**
 * Define DocBlock
 **/


define('BREAK_AND_TABS',2);
define('INLINE_WITH_TABS',1);
define('INLINE',0);

class csml {

	static private $chain = array();
	static private $tabsCount = 0;
	static public $globalFormat = false;
	
	/**
	 * Generate HTML tags using CSS selectors
	 *
	 * @param string $selector 
	 * @param string $comment 
	 * @return void
	 * @author Armando Sosa
	 */
	function tag($selector = "div",$attr='',$format=BREAK_AND_TABS,$comment=''){
		// initialize variables
		$element = $id = $classes = '';
		$tagMask = '<%1$s%4$s%2$s%3$s%5$s>'; // ugly, I know. Translation 1:element, 4: attributes, 2:id, 3:classes, 5:auto close tag
		$autoClose = '';
		$tabs = '';
		
		if (is_array($attr)) {
			$attr = self::attributes($attr);
		}		
		
		// parse bracked attributes;
		list($selector,$attr) = self::getBracketAttributes($selector,$attr);
				
		// separate selector into parts
		list($element,$id,$classes) = self::getSelectorParts($selector);		
		

		// defaul $element to 'div'
		$element = (empty($element))?'div':$element;

		// if there's a closing slash in element name
		$slashPosition = strpos($element,"/");
		// if the slash is at the start,  we skip the attributes processing
		if ( $slashPosition === 0) {
			$idclasses = "$id$classes";
			if (empty($comment) && !empty($idclasses)) {
				$comment = "/$idclasses";
			}

			$id = $classes = $attr = '';
		}else{
			// if the slash is at the end of the element, then set the autoclose variable
			$length = strlen($element)-1;
			if ($slashPosition==$length && $length != 0) {
				$element = str_replace('/','',$element);
				$autoClose = '/';
			}
			$id = (!empty($id))?" id=\"".str_replace('#','',$id)."\"":$id;
			$classes = (!empty($classes))?" class=\"".trim(str_replace('.',' ',$classes))."\"":$classes;				
		}			
		
		// not a closing element
		if ($slashPosition === false) {
			self::$chain[] = $element;
			self::$tabsCount = count(self::$chain);
		}else{
			self::$tabsCount = count(self::$chain);			
			$chain = (array) self::$chain;
			self::$chain = array_slice($chain,0,-1);
		}
		

		$comment = (!empty($comment))?"<!-- $comment -->":$comment;
		$tag = sprintf($tagMask,$element,$id,$classes,$attr,$autoClose);
		$tag = html_entity_decode($tag);

		// formatting options
		
		return self::format($tag.$comment,$format,($slashPosition !== false));
	}
	
	/**
	 * Separates selector from bracketed elements like '[type="text"]'
	 *
	 * @param string $selector 
	 * @param string $attr 
	 * @return void
	 * @author Armando Sosa
	 */
	private function getBracketAttributes($selector,$attr = ''){
		// extract attributes from $selector
		$pattern = "/\[(.+)\]/";
		preg_match($pattern,$selector,$matches);

		if (isset($matches[1])) {
			$attr .= " ".trim($matches[1]);
			$selector = preg_replace($pattern,'',$selector);
		}
		
		return array($selector,$attr);
	}
	
	/**
	 * Separates the parts of the selector: tag, #id and .classes
	 *
	 * @param string $selector 
	 * @return void
	 * @author Armando Sosa
	 */
	private function getSelectorParts($selector){
		// extract element type, id and classes from $selector
		$pattern = "/([^.^#]*)?(#[^.]*)?(\..+)?/";
		preg_match($pattern,$selector,$parts);
		$element = (isset($parts[1])?$parts[1]:'');
		$id = (isset($parts[2])?$parts[2]:'');
		$classes = (isset($parts[3])?$parts[3]:'');
		if ($element == '/') {			
			if (!empty(self::$chain)) {
				$element .= self::$chain[count(self::$chain)-1];
			}
		}
		// get rid of the selector
		return array($element,$id,$classes);
	}
	
	/**
	 * Line breaks and tabs formatting for a selector
	 *
	 * @param string $tag 
	 * @param string $format 
	 * @param string $closing 
	 * @return void
	 * @author Armando Sosa
	 */
	private function format($tag,$format,$closing ){
		$tabs = '';
		
		if (self::$globalFormat !== false) {
			$format = self::$globalFormat;
		}
		
		switch ($format) {
			case BREAK_AND_TABS:
				$tabs = str_pad($tabs,self::$tabsCount-1,"\t");			
				$before = "\n".$tabs;
				$after = "\n";
				break;
			case INLINE_WITH_TABS:
				if ($closing === false) {
					$tabs = str_pad($tabs,self::$tabsCount-1,"\t");			
				}
				$before = ''.$tabs;
				$after = '';
				break;			
			default:
				$before = '';
				$after = '';
				break;
		}		
		return $before.$tag.$after;
	}
		
	/**
	 * Encloses $content in $selector, using self::tag(). If $selector is an array, it gets nested in every tag in the selector array
	 *
	 * @param string $content 
	 * @param string $selector 
	 * @param string $params 
	 * @param string $format 
	 * @param string $comment 
	 * @return void
	 * @author Armando Sosa
	 */
	function entag($content,$selector = "div",$params='',$format=INLINE_WITH_TABS,$comment=''){	
		$return = '';
		$selector = explode('>',$selector);
		if (is_array($selector)) {
			$return = $content;
			$selectors = array_reverse($selector);
			foreach ($selectors as $selector) {
				$selector = trim($selector);
				$return  = self::tag($selector,$params,$format,$comment).$return.self::tag("/$selector",'',$format);
			}
		}else{
			$return  = self::tag($selector,$params,$format,$comment).$content.self::tag("/$selector",'',$format);			
		}
		return $return;
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
		return self::attr($atts);
	}

}

function t($selector = "div",$params='',$format=BREAK_AND_TABS,$comment=''){
	echo csml::tag($selector,$params,$format,$comment);
}

function en($content,$selector = "div",$params='',$format=INLINE_WITH_TABS,$comment=''){
	echo csml::entag($content,$selector,$params,$format,$comment);
}

?>