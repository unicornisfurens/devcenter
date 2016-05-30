<?php

class Meta_Shortcodes {

  public $shortcodes;
  public $is_ccs = true; // Support for Custom Content Shortcode

  function __construct() {
    add_action( 'init', array($this, 'init') );
    add_filter('user_can_richedit', array($this, 'disable_visual_editor'));
  }

  function init() {

    $this->register_post_type();
    $this->create_shortcodes();
  }

  function disable_visual_editor($default) {
    global $post;
    if ('shortcode' == get_post_type($post)) return false;
    return $default;
  }

  function register_post_type() {

    $single = 'Shortcode';
    $plural = 'Shortcodes';

  	$labels = array(
  		'name'                  => $plural,
  		'singular_name'         => $single,
  		'menu_name'             => $plural,
  		'name_admin_bar'        => $single,
  		'archives'              => $single.' Archives',
  		'parent_item_colon'     => 'Parent '.$single.':',
  		'all_items'             => 'All '.$plural.'',
  		'add_new_item'          => 'Add New '.$single,
  		'add_new'               => 'Add New',
  		'new_item'              => 'New '.$single,
  		'edit_item'             => 'Edit '.$single,
  		'update_item'           => 'Update '.$single,
  		'view_item'             => 'View '.$single,
  		'search_items'          => 'Search '.$single,
  		'not_found'             => 'Not found',
  		'not_found_in_trash'    => 'Not found in Trash',
  		'featured_image'        => 'Featured Image',
  		'set_featured_image'    => 'Set featured image',
  		'remove_featured_image' => 'Remove featured image',
  		'use_featured_image'    => 'Use as featured image',
  		'insert_into_item'      => 'Insert into '.$single,
  		'uploaded_to_this_item' => 'Uploaded to this '.strtolower($single),
  		'items_list'            => $plural.' list',
  		'items_list_navigation' => $plural.' list navigation',
  		'filter_items_list'     => 'Filter '.strtolower($plural).' list',
  	);

  	$args = array(
  		'label'                 => $single,
  		'description'           => 'Meta Shortcodes',
  		'labels'                => $labels,
  		'supports'              => array('title', 'editor'),
  		'hierarchical'          => false,
  		'public'                => true,
  		'show_ui'               => true,
  		'show_in_menu'          => true,
  		'menu_position'         => 20,
  		'show_in_admin_bar'     => false,
  		'show_in_nav_menus'     => false,
  		'can_export'            => true,
  		'has_archive'           => true,
  		'exclude_from_search'   => false,
  		'publicly_queryable'    => false,
  		'capability_type'       => 'page',
      'menu_icon'             => 'dashicons-media-code',
  	);

  	register_post_type( 'shortcode', $args );
  }


  function create_shortcodes() {

    $posts = get_posts(array(
      'post_type' => 'shortcode',
      'posts_per_page' => -1,
    ));

    foreach ($posts as $post) {

      $name = $post->post_title; // post_name

      // Already exists
      if ( isset($this->shortcodes[ $name ]) ) continue;

      $this->shortcodes[ $name ] = $post->post_content;

      $callback = array($this, 'meta_shortcode');

      if ($this->is_ccs) add_ccs_shortcode( $name, $callback );
      else add_shortcode( $name, $callback );
    }
  }


  function meta_shortcode( $atts, $content, $name ) {

    if ( ! isset($this->shortcodes[ $name ]) ) return;

    $template = $this->shortcodes[ $name ];

    $atts['content'] = $content;

    foreach ($atts as $key => $value) {
      $tag = '{'.strtoupper($key).'}';
      $template = str_replace($tag, $value, $template);
    }

    return $this->is_ccs ? do_ccs_shortcode($template) : do_shortcode($template);
  }

}

new Meta_Shortcodes;
