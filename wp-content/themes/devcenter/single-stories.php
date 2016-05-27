<<<<<<< HEAD
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php wp_reset_postdata(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<div class="story-cats">
						<ul>
							<?php
								$taxonomies = wp_get_post_terms( get_the_ID(), 'type' );
								foreach($taxonomies as $taxonomy) :
									echo '<li>' . $taxonomy->name . '</li>';
								endforeach;
							?>
						</ul>
					</div>
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					
					<div class="story-types">
						<?php the_category(); ?>
					</div>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<div id="stories-text">
						<?php the_content( sprintf(__( 'Continue reading %s', 'twentyfifteen' ), the_title( '<span class="screen-reader-text">', '</span>', false )) ); ?>
					</div>
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
			) );
			?>

		<?php endwhile; ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

=======
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php wp_reset_postdata(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<div class="story-cats">
						<ul>
							<?php
								$taxonomies = wp_get_post_terms( get_the_ID(), 'type' );
								foreach($taxonomies as $taxonomy) :
									echo '<li>' . $taxonomy->name . '</li>';
								endforeach;
							?>
						</ul>
					</div>
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					
					<div class="story-types">
						<?php the_category(); ?>
					</div>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<div id="stories-text">
						<?php the_content( sprintf(__( 'Continue reading %s', 'twentyfifteen' ), the_title( '<span class="screen-reader-text">', '</span>', false )) ); ?>
					</div>
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
			) );
			?>

		<?php endwhile; ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

>>>>>>> ccc08261a72e6f9daab5a1f686985eb43716cb98
<?php get_footer(); ?>