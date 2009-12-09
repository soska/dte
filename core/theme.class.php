<?php
/**
*  Theme Class
*/
class DuperrificThemeController extends Duperrific {
	
	/**
	 * The name of the theme
	 *
	 * @var string
	 */
	var $name;
	
	/**
	 * Blog object proxy
	 *
	 * @var string
	 */
	var $blog;
	
	/**
	 * Hooks
	 *
	 * @var string
	 */
	var $hooks = array();
	
	/**
	 * hold default options
	 *
	 * @var string
	 */
	var $options = array();
	
	/**
	 * undocumented variable
	 *
	 * @var string
	 */
	private $defaultOptions = array();
	
	/**
	 * List all widgets defined in the theme class to initialize
	 *
	 * @var string
	 */
	var $widgets = array();
	
	
	/**
	 * define widget ready areas
	 *
	 * @var string
	 */
	var $widgetAreas = 'widget-areas';

	/**
	 * set default options for widget-ready areas
	 *
	 * @var string
	 */
	var $defaultWidgetAreaOptions = array(
		   	'name' => 'Widget Area',
			'before_area'=>'<div id="%1$s-widgetarea" class="%2$s"><ul class="%3$s">',
			'after_area'=>'</ul></div><!-- #%1$s .%2$s -->',
	       	'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
	       	'after_widget' => "</li>",
			'before_title' => "<h3 class=\"widget-title\">",
			'after_title' => "</h3>\n",		
			'default' => 'default',
		);
	
		
	/*
		Panels
	*/
	
	var	$panels = array();
	
	var $defaultPanelOptions = array(
			'level'=>'theme_page',
			'pageTitle'=>'Options',
			'menuTitle'=>'Options',
			'accessLevel'=>'edit_themes',
			'scripts'=>array(),
		);
		
	var $autoloadableScripts=array(
		'admin'=>array(
			'jquery',
			'jquery-ui-core',
			'duperrific-options'=>array(DUP_CORE_JS_URL,'options.js'),
			),
	);
	
	var $autoloadableStyles=array(
		'admin'=>array(
			'duperrific-options'=>array(DUP_CORE_STYLES_URL,'options.css'),
		),
	);
	
	var $scripts = array();
	
	/**
	 * Holds data from requests
	 *
	 * @var string
	 */
	var $data = array();
	
	
	/*
		Constructor
	*/
	
	function __construct($name, &$blog){
		$this->name = $name;
		$this->blog = &$blog;
		$blog->theme = &$this;
		$this->initOptions();
		if (is_admin()) {
			$this->initPanels();		
		}
		
		add_action('init',array(&$this,'onInit'));		
		
		$this->setup();
		$this->initWidgets();		
	}
	
	/**
	 * Just a hook
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function setup(){
		
	}

	/**
	 * Just a hook
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function onInit(){
		
	}

		
	/**
	 * grab options from database if they exist, otherwise we'll use the defaults
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function initOptions(){
		// save default options, in case we want to restore them later
		$this->defaultOptions = $this->options;
		if ($options = get_option($this->domain())) {
			$this->options = set_merge($this->options,unserialize($options));
		}
	}
	
	function domain(){
		return "dup.{$this->name}.plist";
	}	
	
	function saveOptions($context=null,$data=null){
		if (!$context || is_array($context)) {
			if (is_array($context)) {
				$data = $context;
			}
			if (!$data) {
				$data = $this->data['option'];
			}
			$context = $this->data['form-name'];
		}
		
		$this->options[$context] = array_merge($this->options[$context],$data);
		update_option($this->domain(),serialize($this->options));
	}
	
	function saveOption($context = null, $key=null, $data = null){
		if (!($context && $key && $data)) {
			return false;
		}
		$this->options[$context][$key] = array_merge($this->options[$context][$key],$data);
		update_option($this->domain(),serialize($this->options));		
	}
	
	function restoreOptions(){
		$context = $this->data['form-name'];
		$this->saveOptions($this->defaultOptions[$context]);
	}
	
	/*
		CRUD Hooks
	*/
	
	/**
	 * this function executes beforesave
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function beforeSave(){
		return true;
	}
	
	/**
	 * saves options
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function save(){
		$this->saveOptions();
		$this->redirect("updated=true");
	}
	
	function restore(){
		$this->restoreOptions();
		$this->setup();
		$this->redirect("restored=true");		
	}
	
	function resetWidgets(){
		update_option( 'sidebars_widgets', null );
		$this->redirect("reseted=true");
		do_action('dup_reset_widgets');
	}
	
	function redirect($query){
		$page = urldecode($this->data['page-name']);
		wp_redirect(admin_url("themes.php?page=$page&$query"));
	}
	
	
	/*
		Widgets
	*/
	
	/**
	 * Initialize Widget Stuff
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function initWidgets(){
		// Register Widgets
		foreach ($this->widgets as $widget) {
			$className = ucfirst($widget)."Widget";
			if (class_exists($className)) {
				register_widget($className);
			}
		}
		
		// let's see if we need a configuration file
		if (is_string($this->widgetAreas)) {
			$path = DUP_CONFIG_PATH."/{$this->widgetAreas}.php";
			if (file_exists($path)) {
				$x = $this->blog->getTextDomain();
				extract($this->blog->getTextDomain());
				include $path;
			}else{
				$this->widgetAreas = array();
			}
		}
						
		// Initialize widget-ready areas
		foreach ($this->widgetAreas as $id => &$widget) {
			if (is_string($widget)) {
				$widget = array('name'=>$widget);
			}

			$widget['id'] = $id;
			$widget = set_merge($this->defaultWidgetAreaOptions,$widget);
			register_sidebar($widget);
		}
	}	

	
	
	/**
	 * Sets a widget ready area for our theme
	 *
	 * @param string $name 
	 * @param string $divClass 
	 * @param string $ulClass 
	 * @return void
	 * @author Armando Sosa
	 */
	function widgetArea($name,$divClass = 'aside', $ulClass = "xoxo", $default = null){

		$hasWidgets = $this->areaHasWidgets($name);
		
		if (isset($this->widgetAreas[$name])) {
			$w = $this->widgetAreas[$name];			
			$longName = $w['name'];
			$w['before'] = sprintf($w['before_area'],$name,$divClass,$ulClass);
			$w['after'] = sprintf($w['after_area'],$name,$divClass);		
			
			// this is a kinda hacky workaraound to avoid displaying anything if the widget area is empty
			echo ($hasWidgets)?$w['before']:'';
		    if (!dynamic_sidebar($name)) {
				// $default holds the view that is rendered in case that no widget is set for this area
				$default = ($default)?$default:$w['default'];			
		    	if ($default){
					$this->blog->renderView("sidebars/$default",array('widget'=>$w));
				}
		    };
			echo ($hasWidgets)?$w['after']:'';		}
	}
	
	/**
	 * Check that the area has any widgets assigned
	 *
	 * @param string $name 
	 * @return void
	 * @author Armando Sosa
	 */
	function areaHasWidgets($name){
		$areas = get_option('sidebars_widgets');
		return !empty($areas[$name]);
	}
	
	/*
		Options Panels
	*/
	
	/**
	 * Intialize the custom options panel stuff
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function initPanels(){
		if (!empty($this->panels)) {
			add_action('admin_menu',array(&$this,'addPanels'));
		}
	}
	
	/**
	 * Add theme option pages
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function addPanels(){
		foreach ($this->panels as $panel => $options) {			
			if (!is_string($panel)) {
				$panel = $options;
				$options = array();
			}
			$options = array_merge($this->defaultPanelOptions,$options);
			$function = array(&$this,$panel);
			$page = add_theme_page( 
				$options['pageTitle'], // title of the panel
				$options['menuTitle'], // the title of the browser window, when the panel is open 
				$options['accessLevel'], // required access level
				underscorize($panel), // the unique name of the page
				$function // the function for the callback
			);
			if (!empty($options['scripts'])) {
				$this->nqScript($page,$options['scripts']);
			}
			if (!empty($options['styles'])) {
				$this->nqStyle($page,$options['styles']);
			}
			add_action("admin_print_scripts-$page", array(&$this,'injectScripts'));
			add_action("admin_print_styles-$page", array(&$this,'injectStyles'));			
		}		
	}
	
	function nqScript($hook = null,$scripts = null){
		if (is_string($hook)) {
			$this->autoloadableScripts[$hook] = $scripts;
		}
	}
	
	function nqStyle($hook = null,$styles){
		if (is_string($hook)) {
			$this->autoloadableStyles[$hook] = $styles;
		}
	}
	
	
	/**
	 * includes options pael template
	 *
	 * @param string $name 
	 * @return void
	 * @author Armando Sosa
	 */
	function includePanel($name,$vars=null){
		$this->blog->renderView("panels/$name",$vars);
	}
		
	/**
	 * Inject styles hook
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	
	function injectStyles(){
		global $page_hook;
		$hooks = array('admin',$page_hook);
		foreach ($hooks as $hook) {
			if (!isset($this->autoloadableStyles[$hook])) {
				continue;
			}
			foreach ((array) $this->autoloadableStyles[$hook] as $name => $file) {
				if (is_int($name)) {
					$name = $file;
					$file = null;
				}else{
					$file = implode('',(array)$file);
				}
	    		wp_enqueue_style($name,$file,null,null,false,'screen');
			}
		}
	}
	
	/**
	 * inject scripts hook
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function injectScripts(){
		global $page_hook;
		$hooks = array('admin',$page_hook);
		foreach ($hooks as $hook) {
			if (!isset($this->autoloadableScripts[$hook])) {
				continue;
			}			
			foreach ((array) $this->autoloadableScripts[$hook] as $name => $file) {
				if (is_int($name)) {
					$name = $file;
					$file = null;
				}else{
					$file = implode('',(array)$file);
				}
	    		wp_enqueue_script($name,$file);									
			}
		}
	}

	
	function style($handle,$options = null){
		$this->dependency('style',$handle,$options);
	}
	
	function script($handle,$options = null){
		$this->dependency('script',$handle,$options);
	}
	
	function dependency($type,$handle,$options = array()){
		$subdir = ($type == 'style')?'/styles/':'/js/';
		$options = set_merge(array(
				'src'=>false,
				'root'=>get_bloginfo('template_directory').$subdir,
				'deps'=>array(),
				'ver'=>false,
				'media'=>false,
				'conditions'=>false,
			),$options);
		
		$options['deps'] = (array) $options['deps'];
		
		if ($options['root']) {
			$src = $options['root'].$options['src'];
		}else{
			$src = $options['src'];
		}
		
		$function = "wp_enqueue_$type";
		
		$function($handle,$src,$options['deps'],$options['ver'],$options['media']);
		
		if ($type = "style") {
			if ($options['conditions']) {
				global $wp_styles;
				foreach ((array) $options['conditions'] as $condition) {
					$wp_styles->add_data( $handle, 'conditional', $condition );						
				}
			}			
		}
	}
	
	
	
}

?>