<?php
/**
*  Options
*/
class OptionsHelper{

	/**
	 *  Panel Name
	 *
	 * @var string
	 */
	var $name;
	/**
	 * Context
	 *
	 * @var string
	 */
	var $context;
	/**
	 * Holds a reference to the blgo object
	 *
	 * @var string
	 */
	var $blog;

	/**
	 * Tag Templates
	 *
	 * @var string
	 */
	var $inputTags = array(
			'text'=>'<input type="text" name="%1$s" value="%2$s" %3$s/>',
			'textarea'=>'<textarea type="text" name="%1$s" %3$s>%2$s</textarea>',
			'hidden'=>'<input type="hidden" name="%1$s" value="%2$s" %3$s/>',
			'select'=>"<select %2\$s>%1\$s</select>",
			'option'=>"<option %2\$s>%1\$s</option>",
			'radio'=>'<label><input type="radio" name="%1$s" value="%3$s"%4$s/>%2$s</label>',			
		);
	/**
	 * indicates if a section is open
	 *
	 * @var string
	 */	
	var $openSection = 0;	
		
	/**
	 * Constructor
	 *
	 * @param string $blog 
	 * @author Armando Sosa
	 */
	function __construct(&$blog){
		$this->blog = &$blog;
	}

	/**
	 * Outputs a flash message if $field is in the query strng, and is set to true
	 *
	 * @param string $msg 
	 * @param string $field
	 * @param string $class
	 * @return void
	 * @author Armando Sosa
	 */
	function flash($msg,$field = "updated",$class="updated fade below-h2"){
		if (isset($_REQUEST[$field]) && (($_REQUEST[$field]=='true') || ($_REQUEST[$field]==1))) {
			return "<div id='message' class='$class'><p>$msg</p></div>";
		}
	}


	/**
	 * Outputs a title
	 *
	 * @param string $title 
	 * @param string $icon if set, it generates a wordpress icon div with that id="$icon"
	 * @return void
	 * @author Armando Sosa
	 */
	function title($title,$icon = null){
		$output = '';
		if ($icon) {
			$output.= "<div class='icon32' id='$icon'><br/></div>";
		}
		$output.= "<h2>$title</h2>";
		return $output;
	}

	function section($title=null,$tag=null){
		$output='';
		if ($this->openSection) {
			$output.=$this->endSection();
		}
		if (is_string($tag)) {
			$output.= HtmlHelper::tag($tag);
			$this->openSection = "/$tag";			
		}else{
			$this->openSection = 1;			
		}
		if ($title) {
			$output.="\n\t<h3>$title</h3>";
		}
		$output.="\n\t<table class=\"form-table\">";
		return $output;
	}
	
	function endSection(){
		$output= "\n\t</table>";
		if (is_string($this->openSection)) {
			$output.= HtmlHelper::tag($this->openSection);
		}
		$this->openSection = 0;		
		return $output;
	}

	/**
	 * Creates an wordpress option form
	 *
	 * @param string $name 
	 * @param string $action 
	 * @param string $method 
	 * @return void
	 * @author Armando Sosa
	 */
	function form($name, $action = null, $method = "post"){

		$this->name = $name;

		if (!$action) {
			$url == 'options.php';
		}else{
			$url=admin_url("admin-post.php?action=$action");			
		}
		$form =  "\n\t\t<form action=\"$url\" method=\"post\">\n";		
		$nonceField = "\n\t\t\t".wp_nonce_field($this->name,"_wpnonce",true,false);
		$nameField =  "\n\t\t\t".$this->input('form-name',array('type'=>'hidden','value'=>$this->name),false);

		$pageNameField = '';
		if (isset($_GET['page'])) {
			$pageNameField =  "\n\t\t\t".$this->input('page-name',array('type'=>'hidden','value'=>urlencode($_GET['page'])),false);
		}
		
		return $form.$nonceField.$nameField.$pageNameField;
	}
	
	
	/**
	 * ends the form
	 *
	 * @param string $submit 
	 * @return void
	 * @author Armando Sosa
	 */
	function formEnd($submit = null){
		$output = "";
		if ($this->openSection) {
			$output.=$this->endSection();
		}		
		if ($submit) {
			$output.= $this->submit($submit);
		}
		$output.= "\n\t\t</form>";
		return $output;
	}
	
	/**
	 * Sets the options context
	 *
	 * @param string $name 
	 * @return void
	 * @author Armando Sosa
	 */
	function context($name){
		$this->context = $name;
		return $this->input('options-context',array('type'=>'hidden','value'=>$name),false);
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
						'type'=>'text',
						'description'=>'',
						'row'=>1,
						'options'=>array(),
						'multiple'=>false,
						'id'=>'',
					),(Array) $options
			);		

		// grab value from options table
		if (!isset($options['value'])) {
			$options['value'] = $this->grabValueFromOptions($name);
		}

		$name = ($prefix)?"option[$name]":$name;

		$options['id'] = (!empty($options['id']))?" id='{$options['id']}'":'';
		
		switch ($options['type']) {
			case 'select':
				$input = $this->_select($name,$options);
				break;
			case 'radio':
				$input = $this->_radio($name,$options);
				break;
			case 'imageRadio':
				$input = $this->_imageRadio($name,$options);
				break;
			default:
				$input = sprintf($this->inputTags[$options['type']],$name,$options['value'],$options['id']);
				break;
		}

		if ($options['type'] == 'hidden') {
			$options['row'] = false;
		}
		if ($options['row']) {
			return $this->inputRow($options['label'],$input,$options['description']);
		}else{
			return $input;
		}
	}	
	
	/**
	 * Formats a select element
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function _select($name,$options){
		$selectAtts = $options['id'];
		$selectAtts.= "name='$name' ";
		$optionFields = '';
		if ($options['multiple']) {
			$selectAtts.="multiple='multiple' ";
		}
		foreach ($options['options'] as $key => $label) {
			$optionAtts = '';
			$optionAtts.="value='$key' ";
			if ($key == $options['value']) {
				$optionAtts.= "selected='selected' ";
			}
			$optionFields.=sprintf($this->inputTags['option'],$label,$optionAtts);
		}
		$field = sprintf($this->inputTags['select'],$optionFields,$selectAtts);
		return $field;
	}
	
	/**
	 * Formats a radio group
	 *
	 * @param string $name 
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */
	function _radio($name,$options){
		$options = array_merge(
				array(
						'class'=>'radio-group',
					),(Array) $options
			);		
		$id=$class='';
		if ($options['id']) $id = " id=\"{$options['id']}\"";
		if ($options['class']) $class = " class=\"{$options['class']}\"";
		$output = "\n\t<div$id$class>";
		foreach ($options['options'] as $value=>$label) {
			$atts='';
			if ($value == $options['value']) {
				$atts = " checked='checked'";
			}
			$output.="\n\t\t\t".sprintf($this->inputTags['radio'],$name,$label,$value,$atts);
		}
		$output .= "\n\t\t</div>";

		return $output;
	}
	
	/**
	 * Image Radio Control
	 *
	 * Enhanced Javascript Control, that allows an array of images, to act as radio
	 *
	 * @param string $name 
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */
	function _imageRadio($name,$options){
		$options = set_merge(
				array(
						'baseDir'=>'',
					),(Array) $options
			);
		$input = HtmlHelper::tag('div.image-radio-group');
		foreach ($options['options'] as $value => $image) {
			$selected = ($value == $options['value'])?'.selected':'';
			$atts = HtmlHelper::attr(array(
					'title'=>$value,
					'src'=>$options['baseDir'].$image,
				));
			$input.=HtmlHelper::tag('img.radio'.$selected,$atts);
		}
		$input .= $this->input($name,array('type'=>'hidden','value'=>$value),false);
		$input .= HtmlHelper::tag('/div.image-radio-group');	
		return $input;
	}
	
	/**
	 * outputs a submit button
	 *
	 * @param string $label 
	 * @return void
	 * @author Armando Sosa
	 */
	function submit($label, $class="button-primary"){
		return "\n\t\t\t\t<p class=\"submit\"><input type=\"submit\" class=\"$class\" value=\"$label\" /></p>";
	}
	
	/**
	 * Creates a table row formatted according to wordpress standards
	 *
	 * @param string $label 
	 * @param string $input 
	 * @param string $description 
	 * @return void
	 * @author Armando Sosa
	 */
	function inputRow($label,$input,$description=''){
		if (!empty($description)) {
			$description = "<span class=\"setting-description\">$description</span><br/>";
		}
		$th="\n\t\t\t\t<th scope=\"row\">$label</th>";
		$td="\n\t\t\t\t<td>$description $input</td>";
		$row = "\n\t\t\t<tr valign=\"top\">$th $td\n\t\t\t</tr>";
		return $row;
	}
	

	/**
	 * generates a list of checkboxes for each category and subcategory
	 *
	 * @param string $name 
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */	
	function categoryChecklist( $name, $options = null) {
		$options = array_merge(
				array(
						'label'=>$name,
						'value'=>null,
						'description'=>'',
						'row'=>1,
						'descendants_and_self'=>0,
					),(Array) $options
			);		

		require_once(DUP_CORE_PATH.'/misc.classes.php');
		$walker = new Dup_Walker_Category_Checklist;

		extract($options);
		$descendants_and_self = (int) $descendants_and_self;

		$args = array();

		$args['fieldName'] = "option[$name][]";
		
		if (!$value) {
			$value = $this->grabValueFromOptions($name);
			if (empty($value)) {
				$value = true;
			}
		}
		
		$args['selected_cats'] = $value;
		

		if ( $descendants_and_self ) {
			$categories = get_categories( "child_of=$descendants_and_self&hierarchical=0&hide_empty=0" );
			$self = get_category( $descendants_and_self );
			array_unshift( $categories, $self );
		} else {
			$categories = get_categories('get=all');
		}
		
		$input = $this->input($args['fieldName'],array('type'=>'hidden','value'=>999999),false); //unexistent id, useful when user selects nothing
		$switches = "<a href='#' class='check-all'>".__('All')."</a> | "."<a href='#' class='check-none'>".__('None')."</a>";
		$input .= "<div id=\"$name\" class=\"checklist\"><div class=\"switches\">$switches</div><ul class=\"parent\">".call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args))."</ul><div>";		
		// call the necessary javascript
		$input .= "<script type=\"text/javascript\">jQuery(function($){jQuery('#$name').checkboxList()});</script>";
		
		return $this->inputRow($label,$input,$description);
	}	
	
	
	function pageChecklist( $name, $options = null) {
		$options = array_merge(
				array(
						'label'=>$name,
						'value'=>null,
						'description'=>'',
						'row'=>1,
						'descendants_and_self'=>0,
					),(Array) $options
			);		

		require_once(DUP_CORE_PATH.'/misc.classes.php');
		$walker = new Dup_Walker_Page_Checklist;

		extract($options);

		$args = array();

		$args['fieldName'] = "option[$name][]";
		
		if (!$value) {
			$value = $this->grabValueFromOptions($name);
			if (empty($value)) {
				$value = true;
			}
		}
		
		$args['selected_cats'] = $value;
		
		$pages = get_pages();

		$input = $this->input($args['fieldName'],array('type'=>'hidden','value'=>999999),false); //unexistent id, useful when user selects nothing

  		// create the switches
		$switches = "<a href='#' class='check-all'>".__('All')."</a> | "."<a href='#' class='check-none'>".__('None')."</a>";
		// create the input box
		$input .= "<div id=\"$name\" class=\"checklist\"><div class=\"switches\">$switches</div><ul class=\"parent\">".call_user_func_array(array(&$walker, 'walk'), array($pages, 0, $args))."</ul><div>";
		// call the necessary javascript
		$input .= "<script type=\"text/javascript\">jQuery(function(){jQuery('#$name').checkboxList()});</script>";
		return $this->inputRow($label,$input,$description);
	}	


	function categoryDropdown( $name, $options = null) {
		$options = array_merge(
				array(
						'label'=>$name,
						'value'=>null,
						'description'=>'',
						'row'=>1,
						'descendants_and_self'=>0,
						'depth'=>0,
						'empty'=>0,					
					),(Array) $options
			);		

		$walker = new Walker_CategoryDropdown;

		extract($options);
		$descendants_and_self = (int) $descendants_and_self;

		$args = array();

		$fieldName = "option[$name]";

		if (!$value) {
			$value = $this->grabValueFromOptions($name);
			if (empty($value)) {
				$value = true;
			}
		}

		$args['selected'] = $value;

		$categories = get_categories('get=all');

		// if ( $descendants_and_self ) {
		// 	$categories = get_categories( "child_of=$descendants_and_self&hierarchical=0&hide_empty=0" );
		// 	$self = get_category( $descendants_and_self );
		// 	array_unshift( $categories, $self );
		// } else {
		// 	$categories = get_categories('get=all');
		// }

		// $input = $this->input($args['fieldName'],array('type'=>'hidden','value'=>999999),false); //unexistent id, useful when user selects nothing
		// $input .= "<div id=\"$name\" class=\"checklist\"><ul class=\"parent\">".call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args))."</ul><div>";

		if ( ! empty($categories) ) {

			if ($options['id']) $id = " id=\"{$options['id']}\"";
			if ($options['class']) $class = " class=\"{$options['class']}\"";

			$depth = $options['depth'];			

			$input = "<select name=\"$fieldName\"$id$class>\n";
			if ($options['empty']) {
				$input .= "\t<option value=\"-1\">{$options['empty']}</option>\n";
			}
			$input .=  call_user_func_array(array(&$walker, 'walk'), array($categories,$depth,$args));
			$input .= "</select>\n";
		}
		return $this->inputRow($label,$input,$description);
	}	

	
	function pageDropdown( $name, $options = null) {
		$options = array_merge(
				array(
						'label'=>$name,
						'value'=>null,
						'description'=>'',
						'row'=>1,
						'id'=>null,
						'class'=>null,
						'descendants_and_self'=>0,
						'depth'=>0,
						'empty'=>'random',
					),(Array) $options
			);	
			
		require_once(DUP_CORE_PATH.'/misc.classes.php');
		$walker = new Walker_PageDropdown;

		extract($options);

		$args = array();

		$fieldName = "option[$name]";
		
		if (!$value) {
			$value = $this->grabValueFromOptions($name);
			if (empty($value)) {
				$value = true;
			}
		}
		
		$args['selected'] = $value;
		
		$pages = get_pages();
		
		if ( ! empty($pages) ) {

			if ($options['id']) $id = " id=\"{$options['id']}\"";
			if ($options['class']) $class = " class=\"{$options['class']}\"";
			
			$depth = $options['depth'];			
			
			$input = "<select name=\"$fieldName\"$id$class>\n";
			if ($options['empty']) {
				$input .= "\t<option value=\"-1\">{$options['empty']}</option>\n";
			}
			$input .=  call_user_func_array(array(&$walker, 'walk'), array($pages,$depth,$args));
			$input .= "</select>\n";
		}

		return $this->inputRow($label,$input,$description);
	}	
	
	
	/**
	 * Grabs a value from the options table
	 *
	 * @param string $optionName 
	 * @return void
	 * @author Armando Sosa
	 */
	function grabValueFromOptions($optionName){
		if (isset($this->blog->theme->options[$this->name][$optionName])) {
			$value =  $this->blog->theme->options[$this->name][$optionName];
			if (is_string($value)) {
				$value = stripslashes($value);
			}
			return $value;
		}else {
			return '';
		}
	}
	
	/*
		Ehanced Controls
	*/
	

	

}

?>