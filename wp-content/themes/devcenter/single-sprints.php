<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php
				$start_date = get_post_meta (get_the_ID(), 'start_date', true);
				$end_date = get_post_meta (get_the_ID(), 'end_date', true);
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<div class="sprint-date">
						<span><?php echo do_shortcode('[field "start_date" date_format="d/m/y"] - [field "end_date" date_format="d/m/y"]'); ?></span>
					</div>
					<?php the_title( '<h1 class="entry-title">Sprint ', '</h1>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<div id="stories-text">
						<?php the_content( sprintf(__( 'Continue reading %s', 'twentyfifteen' ), the_title( '<span class="screen-reader-text">', '</span>', false )) ); ?>
					</div>
					
					<?php $connected = new WP_Query( array('connected_type' => 'stories_to_sprints', 'connected_items' => get_queried_object())); ?>
					
					<div id="stories-nav">
						<?php if ( $connected->have_posts() ) : ?>
							<?php while( $connected->have_posts() ) : $connected->the_post(); ?>
								<?php $categories = wp_get_post_categories( get_the_ID(), array('fields' => 'names') ); ?>
								<?php $terms = get_the_terms( get_the_ID(), 'type'); ?>
								<a href="<?php the_permalink(); ?>" class="story">
									<?php if(!empty($terms)) { ?>
										<span class="type">
											<?php foreach( $terms as $term ) { 
												echo '<span class="term">' . $term->name . '</span>, ';
											}?>
										</span>
									<?php } ?>
									<?php the_title(); ?>
									<?php if(!empty($categories)) { ?>
										<span class="category">
											<?php foreach( $categories as $category ) { 
												echo '<span class="cateroy-item">' . $category . '</span>';
											}?>
										</span>
									<?php } ?>
								</a>
							<?php endwhile; ?>
						<?php endif; ?>
					</div>

					<?php wp_reset_postdata(); ?>
				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
				</footer><!-- .entry-footer -->
			</article><!-- #post-## -->

			<?php
			the_post_navigation( array(
				'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
				'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
			));
			?>

		<?php endwhile; ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>