<?php
/**
 * This Component makes it simple to add metaboxes to write panels, each with one field that maps to a custom field
 *
 * @package default
 * @author Armando Sosa
 */
class SimpleMetaBoxComponent extends DuperrificComponent{
	
	var  $alreadyPrintedAjaxUrl = false;
	
	/**
	 * Setups the component
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function setup(){
		if (is_admin()) {
			add_action('init', array(&$this,'injectScripts'));  
			add_action('init', array(&$this,'injectStyles'));  
		}
		add_action('admin_menu', array(&$this,'register'));  
		$this->ajaxSetup();
	}
	
	function injectScripts(){
		wp_enqueue_script('duperrific-options',DUP_CORE_JS_URL.'options.js',array('jquery'));
		wp_enqueue_script('duperrific-simplemetabox',DUP_CORE_JS_URL.'simplemetabox.js',array('jquery'));
	}	
	
	function injectStyles(){
		wp_enqueue_style('duperrific-simplemetabox',DUP_CORE_STYLES_URL.'simplemetabox.css');
		wp_enqueue_style('duperrific-simplemetabox-ie',DUP_CORE_STYLES_URL.'simplemetabox-ie.css');
		global $wp_styles;
		$wp_styles->add_data( 'duperrific-simplemetabox-ie', 'conditional', 'IE' );						
	}
	
	
	/**
	 * Registers each declared component
	 *
	 * @return void
	 * @author Armando Sosa
	 */
	function register(){
		foreach ($this->settings as $id => &$settings) {
			$this->initSettings($id,$settings);
			add_meta_box($id, $settings['title'], array(&$this,'metabox'), $settings['page'], $settings['context'], $settings['priority']);
			add_action('save_post', array(&$this,'save'));			
		}
	}
	
	/**
	 * loads the settings for the current $id
	 *
	 * @param string $id 
	 * @param string $settings 
	 * @return void
	 * @author Armando Sosa
	 */
	function initSettings($id,&$settings){
		// in case that just the id has been passed
		if (!is_string($id)) {
			$id = $settings;
			$settings = array();
		}
		// merge $settings with some default values
		$settings = set_merge(array(
				'title'=>phraseize($id),
				'label'=>false,
				'field'=>underscorize($id),
				'default'=>'',
				'page'=>'post',
				'context'=>'normal',
				'priority'=>'high',
				'hint'=>'',
				'unique'=>true,
				'type'=>'text',
				'size'=>25,
				'cols'=>40,
				'selectNone'=>'None',
				'beforeRender'=>'',
				'beforeSave'=>'',
			),$settings);		
	}
		
	/**
	 * This is the function that handles the connection between our metabox and WP's admin interface
	 *
	 * @param string $object 
	 * @param string $box 
	 * @return void
	 * @author Armando Sosa
	 */
	function metabox($object,$box){
		global $post;
		
		$id = $box['id'];		
		$settings =& $this->settings[$id];
		$value = get_post_meta($post->ID,$settings['field'],$settings['unique']);
		$settings['value'] = empty($value)?$settings['default']:$value;
		// let the developer include a custom file for his metabox, just in case he has special needs
		if (isset($settings['file'])) {
			$path = DUP_APP_PATH."views/metaboxes/{$settings['file']}.php";
			if (file_exists($path)) {
				include($path);
			}
		}else{
			$this->display($id,$settings);		
		}
	}
	
	/**
	 * Returns a properly formatted wpnonce field
	 *
	 * @param string $id 
	 * @return void
	 * @author Armando Sosa
	 */
	function getNonce($id){
		return wp_nonce_field($id,underscorize($id)."_wpnonce",true,false);		
	}
	
	/**
	 * Displays the image
	 *
	 * @param string $id 
	 * @param string $settings 
	 * @return void
	 * @author Armando Sosa
	 */
	function display($id,$settings){
		$html = new HtmlHelper();		
		echo $this->getNonce($id);
		extract($settings);
		
		// labels looks ugly on normal context, so we use h5's instead
		if($label) echo $html->entag($label,'h5');

		// a jQuery snippet that hides this from the custom fields, a bit ugly, but it works
		$this->__hideFieldFromInterface($field);

		if (!empty($settings['beforeRender'])  && is_callable($settings['beforeRender']) ) {
			$data = call_user_func_array($settings['beforeRender'],array($id,$settings,$this));
		}	

		switch($type){
			
			case 'hint':
				echo $html->entag($hint,'p');
			break;			
			case 'image':
				echo $this->imageBox($id,$settings);
			break;

			case 'text':
				$attributes = $html->attr(array(
						'type'=>'text',
						'name'=>$field,
						'value'=>$value,
						'size'=>$size,										
					)
				);
				echo $html->tag("input/#$field",$attributes);
				if (!empty($hint)) echo $html->tag('p').$hint.$html->tag('/p');
			break;

			case 'textarea':
				$attributes = $html->attr(array(
						'name'=>$field,		
						'cols'=>$cols,
				//inline-styles ugh, I know! but including an external stylesheet for this could be overkill. And this is the admin panel anyway.
						'style'=>'width:98%;', 						
					)
				);
				echo $html->tag("textarea#$field",$attributes,null,null).trim($value).$html->tag('/textarea');
				if (!empty($hint)) echo $html->tag('p').$hint.$html->tag('/p');
			break;			
			default:
			// if the type is a function, just let it go.
			if (is_callable($type)) {
				$data = call_user_func_array($type,array($id,$settings,$this,$html));
			}		
		}
		
	}
	
	function __hideFieldFromInterface($field){
		$fields = (array) $field;
		foreach ($fields as $field) {
			echo "<script type=\"text/javascript\" charset=\"utf-8\">jQuery(function(){jQuery('input[value=$field]').parents('tr').remove();})</script>";			
		}
	}
	
	function imageBox($id,$settings,$ajax = false){
		$html = new HtmlHelper();		
		extract($settings);

		if (!$ajax) {
			// display the hint
			if (!empty($hint)) echo $html->tag('p').$hint.$html->tag('/p');			
			$this->ajaxUrl();
			echo $html->tag("div#{$id}chooser.simple-image-chooser");
				echo $html->tag('input/.button-secondary.refresh',' value="'.__('choose from gallery…').'"');
				echo $html->tag("div#{$id}radiogroup.image-radio-group");
				echo $html->tag("/div");	
				
				$script="<script type=\"text/javascript\" charset=\"utf-8\">";
				$script.="jQuery(function(){";
				// $script.="	alert(jQuery.dupSimpleImageChooser);";
				$script.="	jQuery('#{$id}chooser').dupSimpleImageChooser();";
				$script.="	})";
				$script.="</script>";
				echo $script;
				// echo "<script type=\"text/javascript\" charset=\"utf-8\">jQuery(function(){jQuery('#{$id}chooser').dupSimpleImageChooser();})</script>";					
			echo $html->tag("/div#$field.simple-image-chooser");		
		}else{
			// get the post
			// global $post;			
			$attachments = get_children( array( 'post_parent' => $postId, 'post_type' => 'attachment', 'post_mime_type'=>'image', 'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );
			if (!empty($attachments)) {					
				foreach ($attachments as $attId => $attachment) {
					$selected = ($attachment->guid == $value)?'.selected':'';												
					$atts = $html->attr(array(
							'title'=>$attachment->guid,
							'src'=>$this->getImgThumbSrc($attId,$attachment),
							));
					echo $html->tag('img/.radio'.$selected,$atts);
				}		
				echo "<p class='select-none'>$selectNone</p>";				
				$attributes = $html->attr(array(
						'name'=>$field,		
						'type'=>'hidden',
						'value'=>$value,
					)
				);
				echo $html->tag("input/",$attributes,null,null);
				// javascript that prepares this
				echo "<script type=\"text/javascript\" charset=\"utf-8\">jQuery(function(){jQuery('#{$id}radiogroup').dupImageRadio();})</script>";					
			}else{
				echo "<p class='empty'>".__('No Images has been attached')."</p>";				
			}
		}
	}


	function ajaxUrl(){
		if ($this->alreadyPrintedAjaxUrl) {
			return false;
		}
		$attributes = HtmlHelper::attr(array(
				'name'=>'ajax_url',		
				'type'=>'hidden',
				'value'=>admin_url('admin-ajax.php'),
			)
		);
		echo HtmlHelper::tag("input/",$attributes,null,null);		
		$this->alreadyPrintedAjaxUrl = true;
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $id 
	 * @param string $attachment 
	 * @return void
	 * @author Armando Sosa
	 */
	function getImgThumbSrc($id,$attachment){
		$thumbnail = image_get_intermediate_size($id,'thumbnail');						
		if (!$thumbnail) {
			return $attachment->guid;
		}		
		return $thumbnail['url'];
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $postId 
	 * @return void
	 * @author Armando Sosa
	 */
	function save($postId){
		foreach ($this->settings as $id => $settings) {
			$key = $settings['field'];	
			if (isset($_POST[$key])) {
				if ( !isset($_POST[underscorize($id)."_wpnonce"]) || !wp_verify_nonce( $_POST[underscorize($id)."_wpnonce"], $id )) {  
					return $postId;  
				}
				if ( 'page' == $_POST['post_type'] ) {  
					if ( !current_user_can( 'edit_page', $postId ))  
						return $postId;  
					} else {  
						if ( !current_user_can( 'edit_post', $postId ))  
					return $postId;  
				} 
				
				$data = $_POST[$key];  				
				// let's make available a callback for the developer
				if (!empty($settings['beforeSave'])  && is_callable($settings['beforeSave']) ) {
					$data = call_user_func_array($settings['beforeSave'],array($data,$key,$postId,$this));
				}
				 
				$this->updateField($postId,$key,$data,$settings['unique']);

			}// if isset $_POST[$key]
 		}// foreach
	}

	function updateField($postId,$key,$data = '',$unique = true){
		$meta = get_post_meta($postId,$key);

		if(empty($data)){
			// if no data, we delete the custom field
			delete_post_meta($postId,$key,$data,$unique);
		}elseif (empty($meta)) {
			// no previous existence of the custom field, so we create it
			add_post_meta($postId,$key,$data,$unique);
		}elseif($data != $meta){
			// update an existing custom field
			update_post_meta($postId,$key,$data);
		}		
	}

	function ajaxSetup(){
		foreach ($this->settings as $id => &$settings) {
			$this->initSettings($id,$settings);
			// ajax hook
			$alreadyAjaxHooked = 0;
			if (in_array($settings['type'],array('image')) && !$alreadyAjaxHooked) {
				add_action('wp_ajax_simplemetabox_process',array(&$this,'ajaxProcess'));
				$alreadyAjaxHooked = 1;
			}					
		}				
	}
	
	function ajaxProcess(){	
		// get the metabox Id
		$id = str_replace('chooser','',$_POST['id']);
		// get the post ID
		$postId = $_POST['postId'];
		// get the settings
		$settings = &$this->settings[$id];
		// merge with default settings
		$this->initSettings($id,$settings);
		// get the value
		$value = get_post_meta($postId,$settings['field'],$settings['unique']);
		$settings['value'] = empty($value)?$settings['default']:$value;	
		$settings['postId']	= $postId;

		switch ($settings['type']) {
			case 'image':
				echo $this->imageBox($id,$settings,true);
				break;			
			default:
				# code...
				break;
		}		
		die;
	}
}

?>