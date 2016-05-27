<?php

// Load custom files into the header or footer
function theme_name_scripts() {
	wp_enqueue_script( 'branding', get_stylesheet_directory_uri() . '/js/branding.js', array(), '1.0.0', false );
	wp_enqueue_script( 'branding-website', get_stylesheet_directory_uri() . '/js/branding-website.js', array(), '1.0.0', false );
	wp_enqueue_script( 'dotdotdot.min', get_stylesheet_directory_uri() . '/js/dotdotdot.min.js', array(), '1.0.0', true );
	wp_enqueue_script( 'main', get_stylesheet_directory_uri() . '/js/main.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );

// Add a custom logout link to the main menu
function my_nav_wrap() {
  $wrap  = '<ul id="%1$s" class="%2$s">';
  $wrap .= '%3$s';
  $wrap .= '<li class="logout-link"><a href="'.wp_logout_url().'">Logout</a></li>';
  $wrap .= '</ul>';

  return $wrap;
}

/* Post 2 Post */

function my_connection_types() {
    p2p_register_connection_type( array(
        'name' => 'stories_to_sprints', 
        'from' => 'stories',
        'to' => 'sprints',
    ) );

    p2p_register_connection_type( array(
        'name' => 'sprints_to_milestones', 
        'from' => 'sprints',
        'to' => 'milestones',
    ) );
}
add_action( 'p2p_init', 'my_connection_types', 100 );

/* Custom admin page style */

function my_login_logo() {
  ?>
  <style type="text/css">
    .login h1 a {
      background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/img/devcenterlogo.png');
      padding: 0;
      margin: 0;
      width: 320px;
      height: 100px;
      background-size: contain;
    }
  </style>
  <?php 
}
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function my_custom_dashboard_style() {
  ?>
  <style type="text/css">
    #adminmenu li.wp-menu-separator {
      border-bottom: 1px dashed #666;
      margin: 0;
      height: 1px;
    }
  </style>
  <?php
}
add_action('admin_head', 'my_custom_dashboard_style');

/* Change default image link beaviour */
update_option('image_default_link_type','file');

/**
 * Register our sidebars and widgetized areas.
 *
 */
function arphabet_widgets_init() {

  register_sidebar( array(
    'name'          => 'qa_sidebar',
    'id'            => 'qa_sidebar',
    'before_widget' => '<div class="widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2>',
    'after_title'   => '</h2>',
  ) );
 register_sidebar( array(
    'name'          => 'reputatuion_sidebar',
    'id'            => 'reputatuion_sidebar',
    'before_widget' => '<div class="widget">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2>',
    'after_title'   => '</h2>',
  ) );


}
add_action( 'widgets_init', 'arphabet_widgets_init' );