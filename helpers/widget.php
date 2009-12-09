<?php
/**
* 
*/
class WidgetHelper
{
	
	var $Widget;
	
	
	/**
	 * Tag Templates
	 *
	 * @var string
	 */
	var $inputTags = array(
			'text'=>'<input type="text" name="%1$s" %3$s value="%2$s" />',
			'textarea'=>'<textarea %3$s name="%1$s">%2$s</textarea>',			
			'hidden'=>'<input type="hidden" name="%1$s" value="%2$s" />',
			'select'=>"<select %2\$s>%1\$s</select>",
			'option'=>"<option %2\$s>%1\$s</option>",
		);
	
	
	function __construct(&$widget = null){
		if ($widget) {
			$this->Widget = &$widget;
		}
	}
	
	/**
	 * Creates a wordpress option formated input
	 *
	 * @param string $name 
	 * @param string $options 
	 * @param string $prefix 
	 * @return void
	 * @author Armando Sosa
	 */
	function input($name,$options = null,$prefix = true){
		// default options
		$options = array_merge(
				array(
						'label'=>$name,
						'inline'=>0,
						'type'=>'text',
						'description'=>'',
						'options'=>array(),
						'multiple'=>false,
						'attrs'=>'',
						'show_all_cats'=>__('-- All'),
						'show_none_cats'=>'',
					),(Array) $options
			);		

		// grab value from options table
		if (!isset($options['value'])) {
			$options['value'] = $this->Widget->getOption($name);
		}

		$name = $this->Widget->get_field_name($name);
		
		// parse tag
		switch ($options['type']) {
			case 'select':
				$input = $this->_select($name,$options);
				break;
			case 'categorySelect':	
				$input = $this->_categorySelect($name,$options);
				break;
			default:
				if (is_array($options['attrs']) && !empty($options['attrs'])) {
					$options['attrs'] = HtmlHelper::attr($options['attrs']);
				}
				$input = sprintf($this->inputTags[$options['type']],$name,$options['value'],$options['attrs']);
				break;
		}

		if ($options['type'] == 'hidden') {
			return $input;
		}
		
		if ($options['label']) {
			if ($options['inline']) {
				$output = "<label>{$options['label']} $input </label>";
			}else{
				$output = "<p><label for='$name'>{$options['label']}</label></p>";
				$output.= "<p>$input</p>";
			}
		}else{
			$output = $input;
		}
		
		return $output;
	}	
	
	/**
	 * Formats a select element
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function _select($name,$options){
		$selectAtts = "name='$name' ";
		$optionFields = '';
		if ($options['multiple']) {
			$selectAtts.="multiple='multiple' ";
		}
		foreach ($options['options'] as $key => $label) {
			$optionAtts = "value = \"$key\" ";
			if ($key == $options['value']) {
				$optionAtts.= "selected='selected' ";
			}
			$optionFields.=sprintf($this->inputTags['option'],$label,$optionAtts);
		}
		
		return sprintf($this->inputTags['select'],$optionFields,$selectAtts);		
	}
	
	function _categorySelect($name,$options){
		$select = array(
				'show_option_all'=>$options['show_all_cats'],
				'show_option_none'=>$options['show_none_cats'],
				'hide_empty'=>0,
				'echo'=>0,
				'name'=>$name,
				'selected'=>$options['value'],
				'class'=>'',
			);
		return wp_dropdown_categories( $select );	
	}

}

?>