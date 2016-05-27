<<<<<<< HEAD
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->
					<?php the_content(); ?>
				</artivle>
			<?php endwhile; ?>

			<?php $loop = new WP_Query(array('post_type' => 'releasenotes')); ?>

			<?php if ($loop->have_posts()) : ?>
				<ul>
				<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
				</ul>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

=======
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->
					<?php the_content(); ?>
				</artivle>
			<?php endwhile; ?>

			<?php $loop = new WP_Query(array('post_type' => 'releasenotes')); ?>

			<?php if ($loop->have_posts()) : ?>
				<ul>
				<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
				</ul>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

>>>>>>> ccc08261a72e6f9daab5a1f686985eb43716cb98
<?php get_footer(); ?>