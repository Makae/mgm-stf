<?php

class Makae_GM_STF {
  private static $instance;

  private function __construct() {
    add_action('makae_gm_enqueue', array($this, 'makae_gm_enqueue'));
    add_action('wp_enqueue_scripts', array($this, 'wp_localize_scripts'), 100);
    add_action('wp_ajax_mgm_stf_get_timetable', array($this,'ajax_get_timetable'));
  }

  public static function instance() {
    if(is_null(static::$instance))
      static::$instance = new Makae_GM_STF();

    return static::$instance;
  }

  public function makae_gm_enqueue() {
    $mgm_core = $GLOBALS['MAKAE_GM_CORE'];
    $mgm_core->enqueue_content_provider_style('makae-gm-cp-timetable', MGM_STF_URL . 'css/mgm-stf.css');
    $mgm_core->enqueue_content_provider_script('makae-gm-cp-timetable', MGM_STF_URL .'js/mgm-content.js', array('jquery'));
  }

  public function wp_localize_scripts() {
    $config = array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'ajax_params' => array('action' => 'mgm_stf_get_timetable')
    );
    wp_localize_script('makae-gm-cp-timetable', 'makae_gm_stf', $config);
  }

  public function ajax_get_timetable() {
    $place = array_key_exists('place', $_REQUEST) ? $_REQUEST['place'] : null;

    if(is_null($place))
      throw new Exception("No suitable Place found");

    $list = array(
      array('time' => '09:30', 'label' => 'TV Büren'),
      array('time' => '10:00', 'label' => 'MR Dotzigen'),
      array('time' => '11:00', 'label' => 'TV Busswil'),
      array('time' => '11:30', 'label' => 'DR Hermligen'),
      array('time' => '12:00', 'label' => 'TK Gümmligen'),
    );

    header('Content-Type: text/json; charset=UTF-8');
    $data = array(
      'content' => $this->generateTimeList($list)
    );
    echo json_encode($data);
    die();
  }

  public function generateTimeList($list) {
    $html = '<ul class="timelist">' . "\n";
    foreach($list as $entry)
      $html .= '<li><span class="time">' . $entry['time'] . '</span>' . $entry['label'] . '</li>' . "\n";
    $html .= '</ul>' . "\n";
    return $html;
  }

}