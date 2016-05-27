<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
				$tab_1 = 'milestones';
				$tab_2 = 'sprints';
				$tab_3 = 'stories';
			?>

			<div class="horizontal-tab-nav">
				<ul>
					<li><a href="#" class="horizontal-tab-link-1 active" data-id="1"><?php echo ucfirst($tab_1); ?></a></li>
					<li><a href="#" class="horizontal-tab-link-2" data-id="2"><?php echo ucfirst($tab_2); ?></a></li>
					<li><a href="#" class="horizontal-tab-link-3" data-id="3"><?php echo ucfirst($tab_3); ?></a></li>
				</ul>
			</div>
			
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>

					<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
				</article>
			<?php endwhile; ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>