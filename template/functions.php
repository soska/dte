<?php 
/**
  * Functions file to @@Themename@@ 
  */

include("dte/engine.php");	

$blog = new Controller('@@Themename@@');
extract($blog->getHelpers());
extract($blog->getTextDomain());
?>