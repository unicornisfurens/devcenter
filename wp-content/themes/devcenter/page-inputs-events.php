<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
			$tab_1 = 38;
			$post_id_95 = get_post($tab_1);
			$title_tab_1 = $post_id_95->post_title;

			$tab_2 = 20;
			$post_id_93 = get_post($tab_2);
			$title_tab_2 = $post_id_93->post_title;
		?>

		<div class="horizontal-tab-nav">
			<ul>
				<li><a href="#" class="horizontal-tab-link-1 active" data-id="1">Inputs</a></li>
				<li><a href="#" class="horizontal-tab-link-2" data-id="2">Events</a></li>
			</ul>
		</div>

		<div class="horizontal-tab tab-1">
			<?php
				$args = array('post_type' => 'inputs', 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1); 
				$loop = new WP_Query( $args );
			?>
			<div class="tab-container">
				<ul class="tabs-menu tab-menu-inputs">
					<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
						<li><a href="#tab-<?php echo get_the_ID(); ?>"><?php the_title(); ?></a></li>
					<?php endwhile; ?>
				</ul>
				<div class="tab">
					<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
						<?php $meta = get_post_meta( get_the_ID(), 'meta-for-inputs', true); ?>
						<div id="tab-<?php echo get_the_ID(); ?>" class="tab-content">
							<h2 class="main-title">Spec for <?php the_title(); ?></h2>
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

							<div>
								<?php the_content(); ?>
							</div>

							<footer class="entry-footer">
								<?php twentyfifteen_entry_meta(); ?>
								<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
							</footer><!-- .entry-footer -->
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		</div>

		<div class="horizontal-tab tab-2">
			<?php
				$args = array('post_type' => 'events', 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1); 
				$loop = new WP_Query( $args );
			?>
			<div class="tab-container">
				<ul class="tabs-menu tab-menu-events">
					<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
						<li><a href="#tab-<?php echo get_the_ID(); ?>">Event spec for <?php the_title(); ?></a></li>
					<?php endwhile; ?>
				</ul>
				<div class="tab">
					<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
						<?php $meta = get_post_meta( get_the_ID(), 'meta-for-events', true); ?>
						<div id="tab-<?php echo get_the_ID(); ?>" class="tab-content">
							<h2 class="main-title">Spec for <?php the_title(); ?></h2>
							
							<div>
								<?php the_content(); ?>
							</div>

							<footer class="entry-footer">
								<?php twentyfifteen_entry_meta(); ?>
								<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
							</footer><!-- .entry-footer -->
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		</div>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>