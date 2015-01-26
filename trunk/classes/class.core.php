<?php

class Makae_GM_STF {
  private static $instance;

  private function __construct() {
    add_action('makae_gm_enqueue', array($this, 'makae_gm_enqueue'));
  }

  public static function instance() {
    if(is_null(static::$instance))
      static::$instance = new Makae_GM_STF();

    return static::$instance;
  }

  public function makae_gm_enqueue() {
    $mgm_core = $GLOBALS['MAKAE_GM_CORE'];
    $mgm_core->enqueue_content_provider_style('makae-gm-cp-timetable', plugins_url( __FILE__, 'css/mgm-stf.css'));
    $mgm_core->enqueue_content_provider_script('makae-gm-cp-timetable', plugins_url( __FILE__, 'js/mgm-content.js'), array('jquery'));
  }

}