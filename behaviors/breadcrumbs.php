<?php
/**
 * Breadcrumb Behavior
 *
 * @package default
 * @author Armando Sosa
 */
class BreadcrumbsBehavior extends DuperrificBehavior{
	
	function breadcrumbs(&$blog,$options = array()){
		
		$options = set_merge(array(
				'drop_first_category_node' => 1,
				'search_label'=>'Search',
			),$options);
		
		$pageType = $blog->getCurrentPageType();
		$methodName = "_{$pageType}Breadcrumbs";
		if (method_exists($this,$methodName)) {
			echo $this->{$methodName}($options);
		}
	}
	
	function _list($links,$mask = "<div class='breadcrumbs'><ul>%s\n</ul>\n</div>",$itemMask = "\n<li%s>%s</li>"){
		$list = '';
		$last = count($links);
		$count = 0;
		foreach ($links as $link) {
			$count++;			
			$class = '';			
			if ($count == $last) {
				$class = " class=\"current\"";				
			}
			$list .= sprintf($itemMask,$class,$link);
		}
		return sprintf($mask,$list);
	}
	
	function _categoryBreadcrumbs($options){
		$id = get_query_var('cat');
		$links = $this->_getCategoryParentLinks($id,$options);

		return $this->_list($links);
	}
	
	function _getCategoryParentLinks($id,&$options){
		$links = get_category_parents($id,1,"|");
		$links = explode('|',$links);
		array_pop($links);

		if ($options['drop_first_category_node']) {
			array_shift($links);
		}	
		
		return $links;	
	}
	
	function _singleBreadcrumbs($options){
		$cats = get_the_category();
		$id = $cats[0]->cat_ID;
		$links = $this->_getCategoryParentLinks($id,$options);
		$links[] = HtmlHelper::link(get_the_title(),get_permalink());
		return $this->_list($links);
	}
	
	function _searchBreadcrumbs($options){
		$links = array(
				"<span>{$options['search_label']}</span>",
				"<span>".get_search_query()."</span>",
			);
		return $this->_list($links);
			
	}
}
?>