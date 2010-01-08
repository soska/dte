<?php 
/**
  * Header file to @@Themename@@ 
  */

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<?php
		$blog->element('title');
		$blog->element('meta');	
		wp_head();
	?>	
</head>

<body <?php echo $blog->getBodyClass('@@Themename@@'); ?>>