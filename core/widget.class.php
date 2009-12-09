<?php

/**
 * DuperrificWidget - Base Class for widgets in the duperrific theme framework
 *
 * @package default
 * @author Armando Sosa
 */


class DuperrificWidget extends Wp_Widget{
	
	/**
	 * Name of the Widget, by default it;s set by the constructor to the same as the ClassName
	 *
	 * @var string
	 */
	var $name;
	/**
	 * Title of the widget in WP Admin Panels. Defaults to ::name
	 *
	 * @var string
	 */
	var $title;
	/**
	 * Description of the widget in WP Admin Panel.
	 *
	 * @var string
	 */
	var $description = '';
	/**
	 * Internal underscored version of the name.
	 *
	 * @var string
	 */
	var $slug;
	/**
	 * widget's width
	 *
	 * @var string
	 */
	var $width = 250;
	/**
	 * Whether we should register a control for this widget or not
	 *
	 * @var string
	 */
	var $hasControl = true;
	/**
	 * Array of widget options. 
	 *
	 * Use this variable to define default options for the widget, those will be overriden in run time with values from the db if the exists
	 *
	 * @var string
	 */
	var $options = array();
	
	
	/**
	 * Constructor
	 *
	 * @author Armando Sosa
	 */	
	function __construct(){
		$this->name = get_class($this);
		if (!$this->title) {
			$this->title = $this->name;
		}
		if (empty($this->slug)) {
			$this->slug = str_replace('_widget','',underscorize($this->name));
		}
		
		$widgetOptions = array('classname' => $this->slug, 'description' => $this->description );		
		
		$controlOptions = array( 'width' => $this->width);		
		
		parent::__construct($this->name,$this->title,$widgetOptions, $controlOptions);				
	}
	
	/**
	 * Constructor for PHP4
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function DuperrificWidget(){
		$this->__construct();
	}
	
	/**
	 * grab options from database if they exist, otherwise we'll use the defaults
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function initOptions($instance){
		$this->options = set_merge($this->options,$instance);				
	}
	
	function resetOptions(){
		update_option( $this->domain(), null );		
	}
	
	
	/**
	 * Gets an option from the widget object
	 *
	 * @param string $name 
	 * @return void
	 * @author Armando Sosa
	 */
	function getOption($name, $stripslashes = true){
		if (isset($this->options[$name])) {
			$value = $this->options[$name];
			if (is_string($value) && $stripslashes) {
				$value = stripslashes($value);
			}			
			return $value;						
		}
		return false;
	}
	

	/**
	 * Includes a widget template
	 *
	 * @param string $name 
	 * @param string $args 
	 * @param string $includeHelper 
	 * @return void
	 * @author Armando Sosa
	 */
	function includeTemplate($name,$args = array(),$includeHelper = true){
		$pathToInclude = DUP_VIEWS_PATH."/widgets/$name.php";
		extract($args);
		if ($includeHelper) {
			$widget = $this->loadHelper();
		}
		include($pathToInclude);
	}	
	
	
	/**
	 * Loads the widget helper
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function loadHelper(){
		require_once(DUP_PATH."/helpers/widget.php");
		$className=ucfirst('widget')."Helper";
		return new $className($this);
	}	

	
	function widget($args, $instance){
		$this->initOptions($instance);
		$this->includeTemplate($this->slug,$args);		
	}
	
	
	function form($instance){
		if (!empty($this->options)) {
			$this->initOptions($instance);		
			$this->includeTemplate($this->slug."_control");		
		}else{
			parent::form($instance);
		}
	}
	
	function hook($name, $function){
		$action = array(&$this,$function);
		add_action($name, $action);
	}	
	
}


?>