<?php
/**
 * Add's some useful functions to manipulate pages CMS style
 *
 * @package default
 * @author Armando Sosa
 */
class CmsBehavior extends DuperrificBehavior{
	
	/**
	 * undocumented variable
	 *
	 * @var string
	 */
	var $parentPageId;
	
	/**
	 * Set's the current parent page
	 *
	 * @param string $blog 
	 * @param string $id 
	 * @return void
	 * @author Armando Sosa
	 */
	function setParentPage(&$blog, $id = null){
		if (!is_int($id)) {
			global $post;
			$id = $post->ID;
		}
		$this->parentPageId = $id;
	}
	
	/**
	 * Starts a loop with the child pages defined in ($options['post_parent'])
	 *
	 * @param string $blog 
	 * @param string $options 
	 * @return void
	 * @author Armando Sosa
	 */
	function pageChilds(&$blog,$options = null){
		if ($options && !is_array($options)) {
			$options = array('post_parent'=>$options);
		}else{
			$options = array('post_parent'=>$this->parentPageId);			
		}
		$options = set_merge(array(
			'post_type'=>'page',
			'orderby'=>'menu_order',
			'order'=>'asc'),$options
		);					
		return  $blog->loop($options)->have_posts();		
	}
	
	/**
	 * This is an awful hack to get teasers from pages
	 *
	 * @param string $more_link_text 
	 * @param string $stripteaser 
	 * @param string $more_file 
	 * @return void
	 * @author Armando Sosa
	 */
	function pageTeaser(&$blog,$more_link_text = null, $teaserCustomField = 'TeaserText') {
		global $more,$post;
		$oldMore = $more; // let's try not to break whatever Wp uses this variable for
		$more = 0; // this hack allows to use the teaser feature on pages
		$teaserText = $blog->field($teaserCustomField);
		if (! empty($teaserText)) {
			echo apply_filters('the_content', $teaserText);
			if ( ! empty($more_link_text) )
				echo ' <a href="'. get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>";			
		}else{
			the_content($more_link_text);
		}
		$more = $oldMore;
	}
	
}
?>