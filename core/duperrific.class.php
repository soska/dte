<?php
class Duperrific{
	
	/**
	 * Constructor
	 *
	 * @author Armando Sosa
	 */
	function __construct(){
		$this->hook('init','onInit');
		$this->setup();		
	}
	
	/**
	 * attach a method from this object to a WP hook
	 *
	 * @param string $name 
	 * @param string $function 
	 * @return void
	 * @author Armando Sosa
	 */
	function hook($name, $function){
		$action = array(&$this,$function);
		add_action($name, $action);
	}
	
	/**
	 * runs after the object is constructed
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function setup(){		
	}
	
	/**
	 * Callback
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function onInit(){
	}
	
	
	
}

?>