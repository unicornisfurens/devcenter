<<<<<<< HEAD
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
			$tab_1 = 12;
			$post_id_91 = get_post($tab_1);
			$title_tab_1 = $post_id_91->post_title;
			$content_tab_1 = $post_id_91->post_content;
			$content_tab_1 = apply_filters('the_content', $content_tab_1);
			$content_tab_1 = str_replace(']]>', ']]>', $content_tab_1);

			$tab_2 = 14;
			$post_id_93 = get_post($tab_2);
			$title_tab_2 = $post_id_93->post_title;
			$content_tab_2 = $post_id_93->post_content;
			$content_tab_2 = apply_filters('the_content', $content_tab_2);
			$content_tab_2 = str_replace(']]>', ']]>', $content_tab_2);
		?>

		<div class="horizontal-tab-nav">
			<ul>
				<li><a href="#" class="horizontal-tab-link-1 active" data-id="1"><?php echo $title_tab_1; ?></a></li>
				<li><a href="#" class="horizontal-tab-link-2" data-id="2"><?php echo $title_tab_2; ?></a></li>
			</ul>
		</div>

		<div class="horizontal-tab tab-1">
			<?php echo $content_tab_1; ?>
		</div>

		<div class="horizontal-tab tab-2">
			<?php echo $content_tab_2; ?>
		</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

=======
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
			$tab_1 = 12;
			$post_id_91 = get_post($tab_1);
			$title_tab_1 = $post_id_91->post_title;
			$content_tab_1 = $post_id_91->post_content;
			$content_tab_1 = apply_filters('the_content', $content_tab_1);
			$content_tab_1 = str_replace(']]>', ']]>', $content_tab_1);

			$tab_2 = 14;
			$post_id_93 = get_post($tab_2);
			$title_tab_2 = $post_id_93->post_title;
			$content_tab_2 = $post_id_93->post_content;
			$content_tab_2 = apply_filters('the_content', $content_tab_2);
			$content_tab_2 = str_replace(']]>', ']]>', $content_tab_2);
		?>

		<div class="horizontal-tab-nav">
			<ul>
				<li><a href="#" class="horizontal-tab-link-1 active" data-id="1"><?php echo $title_tab_1; ?></a></li>
				<li><a href="#" class="horizontal-tab-link-2" data-id="2"><?php echo $title_tab_2; ?></a></li>
			</ul>
		</div>

		<div class="horizontal-tab tab-1">
			<?php echo $content_tab_1; ?>
		</div>

		<div class="horizontal-tab tab-2">
			<?php echo $content_tab_2; ?>
		</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

>>>>>>> ccc08261a72e6f9daab5a1f686985eb43716cb98
<?php get_footer(); ?>