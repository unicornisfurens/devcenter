<?php

/*---------------------------------------------
 *
 * Global helper functions
 *
 */


function add_ccs_shortcode( $tag, $func = null, $global = true ) {

  if (is_array($tag)) {
    if ($func === false) $global = false;
    foreach ($tag as $this_tag => $this_func) {
      if ( ! in_array($this_tag, CCS_Plugin::$state['disabled_shortcodes']) )
        add_local_shortcode( 'ccs', $this_tag, $this_func, $global );
    }
  } else {
    if ( ! in_array($tag, CCS_Plugin::$state['disabled_shortcodes']) )
      add_local_shortcode( 'ccs', $tag, $func, $global );
  }
}


function do_ccs_shortcode( $content, $global = true ) {

  $prev = CCS_Plugin::$state['doing_ccs_filter'];
  CCS_Plugin::$state['doing_ccs_filter'] = true;
  //$content = CCS_Format::protect_script($content, $global);
  $content = do_local_shortcode( 'ccs', $content, false );

  CCS_Plugin::$state['doing_ccs_filter'] = $prev; // Restore

  if ( $global ) {
    $content = do_shortcode( $content );
  }

  return $content;
}


if ( function_exists('do_short') ) return;

function do_short( $content = '', $data = array() ) {
  echo get_short( $content, $data );
}

function start_short() { ob_start(); }

function end_short() { echo get_short(); }

function get_short( $content = '', $data = array() ) {

  // $data given as first argument
  if ( is_array($content) ) {
    $data = $content;
    $content = '';
  }

  // Use buffered content
  if ( empty($content) )
    $content = ob_get_clean();

  // Pass data to shortcodes with {KEY}
  foreach ($data as $key => $value) {
    $tag = '{' . strtoupper( $key ) . '}';
    $content = str_replace( $tag, $value, $content );
  }

  return do_ccs_shortcode( $content );
}

function ccs_inspect() {

  // Get the name of caller function and class

  $e = new Exception();
  $trace = $e->getTrace();
  //position 0 would be the line that called this function so we ignore it
  $caller = $trace[1];
  echo (
    '<b>'
      .(!empty($caller['class']) ? $caller['class'].'::' : '')
      .$caller['function']
    .'</b><br>'
  );

  $args = func_get_args();
  ?><pre><code><?php
  foreach ($args as $obj) {
    if (is_string($obj)) {
      echo str_replace(array('[',']','<','>'), array('&#91;','&#93;','&lt;','&gt;'), $obj)."\n";
    } elseif (is_bool($obj)) {
      echo ($obj?'TRUE':'FALSE')."\n";
    } elseif (is_null($obj) ) {
      echo "NULL\n";
    } else {
      print_r($obj);
    }
  }
  ?></code></pre><?php
}
