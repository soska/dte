<?php
class Duperrific{
	
	function Duperrific(){
		$this->__construct();
	}
	
	function __construct(){
	}
	
	function hook($name, $function){
		$action = array(&$this,$function);
		add_action($name, $action);
	}
	
	
}

?>