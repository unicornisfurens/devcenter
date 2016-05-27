<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'content', get_post_format() ); ?>

			<?php
				$connected = new WP_Query( array(
				    'connected_type' => 'sprints_to_milestones', 
				    'connected_items' => get_queried_object()
				));				
			?>
			
			<?php if ( $connected->have_posts() ) : ?>
				<div id="stories-nav">
					<?php while( $connected->have_posts() ) : $connected->the_post(); ?>
						<?php
						$connected2 = new WP_Query( array(
						    'connected_type' => 'stories_to_sprints', 
						    'connected_items' => $post
						));	
						if ( $connected2->have_posts() ) :
							while( $connected2->have_posts() ) : $connected2->the_post();
								?>
								<a href="<?php the_permalink(); ?>" class="story">
									
									<span class="type">
											<?php
											$taxonomies = wp_get_post_terms( get_the_ID(), 'type' );
											foreach($taxonomies as $taxonomy) :
												echo $taxonomy->name;
											endforeach;
											?>
									</span>
									
									<?php the_title(); ?>
									<span class="category">
										<?php
											$categories = get_the_category();
											foreach ( $categories as $category ) { 
											    echo $category->name; 
											}
										?>
									</span>
								</a>
								
								<?php
							endwhile;
						endif;
					    ?>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

			<?php
			the_post_navigation( array(
				'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
				'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
			) );
			?>

		<?php endwhile; ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>