<?php
class DuperrificComponent extends Duperrific{
	
	/**
	 * Contain the settings for the component
	 *
	 * @var string
	 */	
	var $settings = array();
	
	/**
	 * Construct the component, and attaches itself to the $blog object
	 *
	 * @param string $name 
	 * @param string $settings 
	 * @param string $blog 
	 * @author Armando Sosa
	 */
	function __construct($settings,&$controller){
		$this->settings = set_merge($this->settings,$settings);
	}

	/**
	 * Executed right after component is constructed
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function setup(){
				
	}
}

?>