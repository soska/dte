<?php

class DuperrificView extends Duperrific{

	var $Controller;

	var $autoRender = true;
	var $didRender = false;
	var $vars = array();
	
	var $__yield = '';
	
	var $layout = 'default';

	/**
	 * helpers an array of helpers to autoload
	 *
	 * @var string
	 */
	var $helpers = array('html', 'options');

	
	/**
	 * holds internal array of helpers
	 *
	 * @var string
	 */
	private $__helpers;	
	
	/**
	 * undocumented function
	 *
	 * @param string $controller 
	 * @author Armando Sosa
	 */
	function __construct($controller){
		$this->Controller = $controller;
		$this->Controller->View = $this;
		$this->helpers = $controller->helpers;
	}	
	
	/**
	 * Load every helper defined in DuperrificBlog::helpers
	 *
	 * @param array $helpers 
	 * @return void
	 * @author Armando Sosa
	 */	
	function __loadHelpers($helpers = null){
		if (!$helpers) {
			$helpers = $this->helpers;
		}
		foreach ($helpers as $helper) {
			$this->__loadHelper($helper);
		}
		return $this->__helpers;
	}
	
	/**
	 * Loads a helper
	 *
	 * @param string $helper 
	 * @return void
	 * @author Armando Sosa
	 */
	
	function __loadHelper($helper){
		$helper = underscorize($helper);
		require_once(DUP_PATH."/helpers/$helper.php");
		$className=ucfirst($helper)."Helper";
		$this->__helpers[$helper] = new $className($this);
	}


	
	/**
	 * undocumented function
	 *
	 * @param string $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function getHelpers($helpers = null){
		if (!is_array($helpers)) {
			$helpers = $this->__helpers;
		}else{
			$helpers = $this->__loadHelpers($helpers);
		}
		return $helpers;		
	}
	
	/**
	 * Sets the vars that are going to be passed to the view when rendered.
	 *
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 * @author Armando Sosa
	 */
	function set($key,$value = null){
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				if (!is_string($k)) {
					$k = $v;
					$v = $value;
				}
				$this->vars[$k] = $v;
			}
		}else{
			$this->vars[$key] = $value;
		}		
	}
	
	/**
	 * Render a File
	 *
	 * @param string $name 
	 * @param mixed $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function __renderFile($file,$vars=array(),$helpers=null){
		if (file_exists($file)) {
			// include default helpers
			if (!($helpers === false)) {
				extract($this->getHelpers());
			}
			// extract view vars			
			extract($this->vars, EXTR_REFS);			
			// extract vars passed			
			extract((Array) $vars);
			// initialize magical variable $_e with the theme name
			extract($this->Controller->getTextDomain());
			include($file);
		}else{
			trigger_error("View template not found in $file");
		}
	}
		
	function exists($name,$type = 'view'){
		$path = $name = $name.".php";
		if ($type == 'element') {
			$path = DUP_ELEMENTS_PATH."/$name";
		}elseif($type == 'view'){
			$path = DUP_VIEWS_PATH."/$name";
		}elseif($type == 'layout'){
			$path = DUP_LAYOUTS_PATH."/$name";
		}
		return file_exists($path);
	}	
		
	/**
	 * Renders a View
	 *
	 * @param string $name 
	 * @param mixed $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function render($name = null,$vars=array(),$helpers=null){
		if (!$name) {
			$name = $this->Controller->action;
		}
		$file = $name.".php";		
		ob_start();
		$this->__renderFile(DUP_VIEWS_PATH."/$file",$vars);
		$this->__yield = ob_get_contents();
		ob_end_clean();		
		$this->didRender = true;
		if ($this->exists($this->layout,'layout')) {
			$file = DUP_LAYOUTS_PATH."/".$this->layout.".php";
			$this->__renderFile($file);
		}
	}
	
	
	/**
	 * Renders a view element
	 *
	 * @param string $name 
	 * @param mixed $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function element($name,$vars = array()){
		$file = $name.".php";
		ob_start();
		$this->__renderFile(DUP_ELEMENTS_PATH."/$file",$vars);
		$yield = ob_get_contents();
		ob_end_clean();
		return $yield;
	}	
	
	/**
	 * Outputs the view render output
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function yield(){
		echo $this->__yield;
	}	
	
}
?>