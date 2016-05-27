<<<<<<< HEAD
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $meta = get_post_meta( get_the_ID(), 'meta-for-inputs', true); ?>
				
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php if(!empty($meta[0][returntype])) : ?>
						<div class="spec">
							<h3>ReturnType</h3>
							<p><?php echo $meta[0][returntype]; ?></p>
						</div>
						<?php endif; ?>
						<?php if(!empty($meta[0][returndescription])) : ?>
						<div class="spec">
							<h3>ReturnDescription</h3>
							<p><?php echo $meta[0][returndescription]; ?></p>
						</div>
						<?php endif; ?>
						
						<?php the_content(); ?>
					</div><!-- .entry-content -->

					<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
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

<?php get_footer(); ?>
=======
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $meta = get_post_meta( get_the_ID(), 'meta-for-inputs', true); ?>
				
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php if(!empty($meta[0][returntype])) : ?>
						<div class="spec">
							<h3>ReturnType</h3>
							<p><?php echo $meta[0][returntype]; ?></p>
						</div>
						<?php endif; ?>
						<?php if(!empty($meta[0][returndescription])) : ?>
						<div class="spec">
							<h3>ReturnDescription</h3>
							<p><?php echo $meta[0][returndescription]; ?></p>
						</div>
						<?php endif; ?>
						
						<?php the_content(); ?>
					</div><!-- .entry-content -->

					<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
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

<?php get_footer(); ?>
>>>>>>> ccc08261a72e6f9daab5a1f686985eb43716cb98
