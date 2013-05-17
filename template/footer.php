<?php 
/**
  * Footer file to @@Themename@@ 
  */

?>		<div id="footer">
			<?php echo $blog->getOption('general','footer_text') ?>
		</div>
	</div>
	<!--[if lt IE 7]>
		<script type="text/javascript" charset="utf-8">
			<?php $shim = get_bloginfo('template_directory')."/styles/images/blank.gif"; ?>
			jQuery('img.logo').supersleight({shim: '<?php echo $shim ?>',backgrounds:false});
		</script>	
	<![endif]-->

</body>
</html>
<!-- footer	 -->