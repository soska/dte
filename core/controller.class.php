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
	 * Holds a Theme Object
	 *
	 * @var string
	 */
	var $theme;
	
	
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
		if (is_admin()) {
			$this->__adminInit();
		}
		$this->__loadBehaviors();
		$this->__loadHelpers();
		$this->__loadComponents();
		if ($alias) {
			$this->alias = $alias;
		}
		
		add_action('init',array(&$this,'onInit'));
		
		$this->setup();
	}
	
	function __call($method,$arguments){
		if (isset($this->__mappedMethods[$method])) {
			$object = $this->__mappedMethods[$method];
			$argumentsCount = count($arguments);
			switch ($argumentsCount) {
				case 0:
					return $this->__behaviors[$object]->$method($this);
					break;

				case 1:
					return $this->__behaviors[$object]->$method($this,$arguments[0]);
					break;
				
				case 2:
					return $this->__behaviors[$object]->$method($this,$arguments[0],$arguments[1]);
					break;
				
				case 3:
					return $this->__behaviors[$object]->$method($this,$arguments[0],$arguments[1],$arguments[2]);
					break;
				
				case 4:
					return $this->__behaviors[$object]->$method($this,$arguments[0],$arguments[1],$arguments[2],$arguments[3]);
					break;
				
				default:
					return $this->__behaviors[$object]->$method($this,$arguments);
					break;
			}
		}
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
	 * Render a File
	 *
	 * @param string $name 
	 * @param mixed $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function renderFile($name,$vars=array(),$helpers=null){
		// include default helpers
		if (!($helpers === false)) {
			extract($this->getHelpers());
		}
		// extract vars passed
		extract((Array) $vars);
		// initialize magical variable $_e with the theme name
		extract($this->getTextDomain());
		$_e = $this->theme->name.".theme";
		${$this->alias} = &$this;
		include($name);
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
				$domain => $this->theme->name.".theme",
			);
	}
	
	function localize(){
		extract($this->getTextDomain());
		load_theme_textdomain($_e,DUP_L10N_PATH);
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
	 * replacement for WP's native get_* functions
	 *
	 * @param string $name 
	 * @return void
	 * @author Armando Sosa
	 */
	function get($name,$vars = array()){
		extract($this->getHelpers());
		extract($this->getTextDomain());
		$this->renderFile(STYLESHEETPATH."/$name.php",$vars);
	}
	/**
	 * Renders a View
	 *
	 * @param string $name 
	 * @param mixed $helpers 
	 * @return void
	 * @author Armando Sosa
	 */
	function renderView($name,$vars=array(),$helpers=null){
		$this->renderFile(DUP_VIEWS_PATH."/$name.php",$vars);
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
		$this->renderFile(DUP_ELEMENTS_PATH."/$name.php",$vars);
	}
		
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Armando Sosa
	 */	
	function _save(){
		$this->theme->data = $_POST;
		$formName = $this->theme->data['form-name'];
		$method = $formName."Save";
		if ($this->theme->beforeSave()) {
			if (method_exists($this->theme,$method)){
				$this->theme->{$method}();
			}else{
				$this->theme->save();
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
		$this->theme->data = $_POST;
		$formName = $this->theme->data['formName'];
		$method = $formName."Restore";
		if (method_exists($this->theme,$method)){
			$this->theme->{$method}();
		}else{
			$this->theme->restore();
		}			
	}
	
	function _reset_widgets(){
		$this->theme->data = $_POST;
		$formName = $this->theme->data['formName'];		
		$this->theme->resetWidgets();
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
		
		if (isset($this->theme->options[$context][$name])) {
			$value = $this->theme->options[$context][$name];		
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

		
	/*
		Helper Functions
	*/
	
	
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
	 * undocumented class
	 *
	 * @package default
	 * @author Armando Sosa
	 */
	function getBodyClass($classes = null){
		// Wordpress Added a simlar function to the core, so this is not necessary anymore				
		body_class($classes);
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
			$availablePages=array(

					'home',
					'single',
					'paged',					
					'page',
					'year',
					'month',
					'day',
					'date',					
					'time',
					'author',
					'category',
					'tag',
					'tax',
					'archive',										
					'search',
					'feed',
					'comment_feed',
					'trackback',
					'home',
					'404'=>'not-found',
					'admin',
					'attachment',
					'singular',
					'robots',
					'posts_page',
				);
		}
		$pageNames=array();
		foreach ($availablePages as $key => $pageName) {
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