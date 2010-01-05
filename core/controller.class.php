<?php
/**
 * Duperrific Blog Class
 *
 * @package default
 * @author Armando Sosa
 */
class DuperrificController extends Duperrific{
	
	/**
	 * How the alias variable is going to be called
	 *
	 * @var string
	 */
	var $alias = 'blog';

	/**
	 * Current action being performed
	 *
	 * @var string
	 */
	var $action;
	
	/**
	 * Holds a Theme Object
	 *
	 * @var string
	 */
	var $viewClass = 'View';

	/**
	 * Holds a Theme Object
	 *
	 * @var string
	 */
	var $Theme;
	
	/**
	 * helpers an array of helpers to autoload
	 *
	 * @var string
	 */
	var $helpers = array('html');

	/**
	 * components to autoload;
	 *
	 * @var string
	 */
	var $components = array();
	
	/**
	 * internal array of component objects;
	 *
	 * @var string
	 */
	private $__components;
	
	/**
	 * components to autoload;
	 *
	 * @var string
	 */
	var $actsAs = array();
	
	/**
	 * internal array of component objects;
	 *
	 * @var string
	 */
	private $__behaviors;
	
	/**
	 * Map methods to the behaviors
	 *
	 * @var string
	 */
	var $__mappedMethods;

	/**
	 * Hold the different loop objects
	 *
	 * @var string
	 */
	private $_loops = array();
	
	/**
	 * adminHooks
	 *
	 * @var string
	 */
	private $adminHooks = array('save','delete','restore','reset_widgets');
	
	/**
	 * Constructor
	 *
	 * @param string $name Name of the current theme
	 */
		
	function __construct($name,$alias = null){
		new ThemeController($name,$this);
		
		$viewClass = $this->viewClass;
		$viewFile = strtolower($viewClass);
		if (!dupLoad($viewFile)) {
			$viewClass .= 'View';
			$viewFile = "view.".$viewFile;
			if (!dupLoad($viewFile)) {
				trigger_error("$viewClass class missing (expected on $viewFile)",E_USER_NOTICE);
				exit;
			}
		}

		new $viewClass($this);


		if (is_admin()) {
			$this->__adminInit();
		}

		$this->__loadBehaviors();
		$this->View->__loadHelpers();
		$this->__loadComponents();

		if ($alias) {
			$this->alias = $alias;
		}

		parent::__construct();
	}
	
	function __call($method,$arguments){
		if (isset($this->__mappedMethods[$method])) {
			$object = $this->__mappedMethods[$method];			
			return call_user_func_array(array($this->__behaviors[$object],$method),$arguments);
		}
		
		trigger_error("Controller::$method is not a valid function",E_USER_NOTICE);
	}
	

	/**
	 * Initialize hooks for wp-admin
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function __adminInit(){
		// admin_post hooks
		foreach ($this->adminHooks as $hook) {
			$hookName = "admin_post_".$hook;
			add_action($hookName,array(&$this,"_$hook"));
		}		
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function __loadBehaviors(){
		foreach ((Array) $this->actsAs as $behavior=>$settings) {
			if (!is_string($behavior)) {
				$behavior = $settings;
				$settings = array();
			}
			$this->__loadBehavior($behavior,$settings);
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $component 
	 * @param string $settings 
	 * @return void
	 * @author Armando Sosa
	 */
	function __loadBehavior($behavior,$settings = array()){
		$fileName = underscorize($behavior);
		$behavior = ucfirst($behavior);
		$className = $behavior."Behavior";
		if(dupLoad("behaviors/$fileName",DUP_PATH)){
			new $className($behavior,$settings,$this);
		}
	}
	
	
	function registerBehavior(&$behavior){
		$this->__behaviors[$behavior->name] =& $behavior; 
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function __loadComponents(){
		// pr($this->components);
		foreach ($this->components as $component=>$settings) {
			if (!is_string($component)) {
				$component = $settings;
				$settings = array();
			}
			$this->__loadComponent($component,$settings);
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $component 
	 * @param string $settings 
	 * @return void
	 * @author Armando Sosa
	 */
	function __loadComponent($component,$settings = array()){
		$fileName = underscorize($component);
		$component = ucfirst($component);
		$className = $component."Component";
		if(dupLoad("components/$fileName",DUP_PATH)){			
			$this->{$component} =& new $className($settings,$this);
			$this->{$component}->setup();
		}
	}
		
	
	/**
	 * variable for the text domain
	 *
	 * @param string $domain 
	 * @return void
	 * @author Armando Sosa
	 */
	function getTextDomain($domain = '_e'){
		return array(
				$domain => $this->Theme->name.".theme",
			);
	}
	
	/**
	 * starts WP's localization 
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function localize(){
		load_theme_textdomain($this->getTextDomain(),DUP_L10N_PATH);
	}
	
	/**
	 * dispatch the correct view
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function dispatch(){
		$actions = $this->getCurrentPageTypes();
		$actions[] = 'index';
		foreach ($actions as $action) {
			
			$method = "on".ucfirst($action);			
			
			if ($action == 'attachment') {
				global $post;
				list($attachmentType)  = explode('/',$post->post_mime_type);
				$attachmentMethod = "on".ucfirst($attachmentType);
				if ($this->__hasMethod($attachmentMethod)) {
					$method = $attachmentMethod;
					$action = $attachmentType;
				}
			}

			$this->action  = $action;

			if ($this->__hasMethod($this,$method)) {
				global $post;
				$this->$method($post);
				if ($this->View->autoRender && !$this->View->didRender && $this->View->exists($action)) {
					$this->View->render($action);				
				}
				break;
			}elseif($this->View->exists($action)){
				$this->View->render($action);					
				break;
			}
		}
	}
	
	function __hasMethod($object, $method = null){
		if (!is_object($object)) {
			$method = $object;
			$object = $this;
		}

		if (method_exists($object, $method) || array_key_exists($method, $this->__mappedMethods)) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * sets a view variable
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function set($key,$value = null){
		return $this->View->set($key,$value);
	}

		
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */	
	function _save(){
		$this->Theme->data = $_POST;
		$formName = $this->Theme->data['form-name'];
		$method = $formName."Save";
		if ($this->Theme->beforeSave()) {
			if (method_exists($this->Theme,$method)){
				$this->Theme->{$method}();
			}else{
				$this->Theme->save();
			}			
		}else{
			/*
				TODO Throw validation errors?
			*/
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function _restore(){
		$this->Theme->data = $_POST;
		$formName = $this->Theme->data['formName'];
		$method = $formName."Restore";
		if (method_exists($this->Theme,$method)){
			$this->Theme->{$method}();
		}else{
			$this->Theme->restore();
		}			
	}
	
	function _reset_widgets(){
		$this->Theme->data = $_POST;
		$formName = $this->Theme->data['formName'];		
		$this->Theme->resetWidgets();
	}

	/**
	 * grabs an option from the themes option table
	 *
	 * @return void
	 * @author Armando Sosa
	 */	
	function getOption($context,$name=null,$stripslashes = true){
		
		if (!$name) {
			list($context,$name) = explode('.',$context);
		}
		
		if (isset($this->Theme->options[$context][$name])) {
			$value = $this->Theme->options[$context][$name];		
			if (is_string($value) && $stripslashes) {
				$value = stripslashes($value);
			}
			return $value;
		}
	}
	
	/**
	 * makes possible to have multiple custom loops
	 *
	 * @param string $name 
	 * @param string $query 
	 * @return void
	 * @author Armando Sosa
	 */
	function loop($name = 'default',$query = null){
	
		// if we want to use the 'default' loop we use the only parameter as a query
		if (!$query && is_array($name) ) {
			$query = $name;
			$name = 'default';
		}
	
		if (isset($this->_loops[$name])) {
			// just return it, so it can be chained
			return $this->_loops[$name];
		}else{
			if (is_array($query)) {
				$this->_loops[$name] = new WP_QUERY($query);
				return $this->_loops[$name];
			}
		}
	
	}	


	/**
	 * undocumented function
	 *
	 * @param string $key 
	 * @param string $unique 
	 * @param string $object 
	 * @return void
	 * @author Armando Sosa
	 */
	function field($key,$unique=true,$object=null){
		$key = underscorize($key);
		if (!$object) {
			global $post;
		}else{
			$post = &$object;
		}
		return get_post_meta($post->ID,$key,$unique);
	}
	

	
	/**
	 * undocumented function
	 *
	 * @param string $default 
	 * @param string $returnFirstCoincidence 
	 * @param string $availablePages 
	 * @return void
	 * @author Armando Sosa
	 */
	function getCurrentPageType($default='home',$returnFirstCoincidence = true,$availablePages = null){
		if (!$availablePages) { //make this available as a parameter, just in case dev want to change priority order
			$possiblePages=array(
					'home','attachment','single','paged','page','year','month','day',
					'date','time','author','category','tag','tax','archive',
					'search','feed','comment_feed','trackback',
					'404'=>'not-found','admin','singular',
					'robots','posts_page',
				);
		}
		$pageNames=array();
		foreach ($possiblePages as $key => $pageName) {
			$functionName="is_".$pageName;			
			if (is_string($key)) {
				$functionName="is_".$key;
			}
			if (function_exists($functionName) && $functionName()) {
				if ($returnFirstCoincidence) {
					return $pageName;
				}
				$pageNames[] = $pageName;
			}
		}
		
		
		if (!empty($pageNames)) {
			return $pageNames;
		}
		
		return $default;
	}	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function getCurrentPageTypes(){
		return (Array) $this->getCurrentPageType(null,false);
	}
	
	/*
		Styles
	*/
	
	function import($type = null , $name = null, $charset = "utf-8"){
		switch ($type) {
			case 'style':
				$attribs = HtmlHelper::attr(array(
						'rel'=>'stylesheet',
						'href'=>DUP_CORE_STYLES_URL."$name.css",
						'type'=>'text/css',
						'media'=>'screen',
						'title'=>phraseize($name),
						'charset'=>$charset,
					));
				return HtmlHelper::tag('link/',$attribs);
				break;			
			case 'script':
				$attribs = HtmlHelper::attr(array(
						'src'=>DUP_CORE_STYLES_URL."$name.css",
						'type'=>'text/javascript',
						'charset'=>$charset,
					));
				return HtmlHelper::tag('script',$attribs,null,null,null) . HtmlHelper::tag('/script',null,null,null);			
				break;
		}
	}
}
	
?>