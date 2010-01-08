<?php $blog->get('header'); ?>
		<div id="content" >
			<?php
				if (have_posts()){
					while (have_posts()){
					 	the_post(); 		
						$blog->renderView('entry');
					} 
				}else{
					$blog->renderView('404');	
				}			
			?>
		</div>
<?php
$blog->get('sidebar'); 
$blog->get('footer'); 
?>