<?php
/**
 * Category Walker CheckList for options helper
 *
 * An ugly function, but apparently it's the WP's way of handling category trees
 *
 * @package default
 * @author Armando Sosa
 */
class Dup_Walker_Category_Checklist extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $category, $depth, $args) {

		extract($args);
		
		$checked = "";
		if ($selected_cats === true || in_array( $category->term_id, $selected_cats )) {
			$checked = ' checked="checked"';
		}

		$output .= "\n<li>"; 
		$output .= '<label class="selectit"><input value="' . $category->term_id;
		$output .= '" type="checkbox" name="'.$fieldName.'" id="in-category-' . $category->term_id . '"';
		$output .= $checked . '/> ';
		$output .= wp_specialchars( apply_filters('the_category', $category->name )) . '</label>';
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}


class Dup_Walker_Page_Checklist extends Walker {
	var $tree_type = 'page';
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	function start_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth, $args) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $page, $depth, $args) {

		extract($args);
		
		$checked = "";
		if ($selected_cats === true || in_array( $page->ID, $selected_cats )) {
			$checked = ' checked="checked"';
		}

		$output .= "\n<li>"; 
		$output .= '<label class="selectit"><input value="' . $page->ID;
		$output .= '" type="checkbox" name="'.$fieldName.'" id="in-page-' . $page->ID . '"';
		$output .= $checked . '/> ';
		$output .= wp_specialchars( apply_filters('the_title', $page->post_title )) . '</label>';
	}

	function end_el(&$output, $category, $depth, $args) {
		$output .= "</li>\n";
	}
}

?>