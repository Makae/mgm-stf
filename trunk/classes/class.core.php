<?php

class Makae_GM_STF {
  private static $instance;

  private function __construct() {
    add_action('makae_gm_enqueue', array($this, 'makae_gm_enqueue'));
    add_action('wp_enqueue_scripts', array($this, 'wp_localize_scripts'), 100);
    add_action('admin_enqueue_scripts', array($this, 'wp_localize_scripts'), 100);
    add_action('wp_ajax_mgm_stf_get_timetable', array($this,'ajax_get_timetable'));
    add_action('wp_ajax_nopriv_mgm_stf_get_timetable', array($this,'ajax_get_timetable'));
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
      'ajax_params' => array('action' => 'mgm_stf_get_timetable'),
      'settings' => array(
        'places' => $this->get_places()
      )
    );
    wp_localize_script('makae-gm-cp-timetable', 'makae_gm_stf', $config);
  }

  public function ajax_get_timetable() {
    $place = array_key_exists('place', $_REQUEST) ? $_REQUEST['place'] : null;
    $num = array_key_exists('num', $_REQUEST) ? $_REQUEST['num'] : 5;
    $page = array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1;

    if(is_null($place))
      throw new Exception("No suitable Place found");

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $discipline = $this->get_place_name($place);
    $list = $this->get_timetable($place, $num, $page);
    $total = $this->get_timetable($place, $num, $page, true);
    $current = $page * $num;

    header('Content-Type: text/json; charset=UTF-8');
    $data = array(
      'has_more' => $current < $total,
      'content' => $this->generate_content($list, $discipline)
    );
    echo json_encode($data);
    die();
  }

  public function generate_content($list, $title) {
    $list = $this->generate_timelist($list);
    $html = '<h5>' . $title . '</h5>';
    $html .= $list;
    return $html;
  }

  public function get_timetable($place, $num=5, $page=1, $count=false) {
    if($count) {
      $select = 'SELECT COUNT(*) ' . "\n";
      $limit = '';
    } else {
      $select = 'SELECT CONCAT(`prefix`, " ", `ort`, " (", riegen_nr, ")") as label, startzeit, endzeit ' . "\n";
      $limit = 'LIMIT {%from%}, {%num%}';
    }
    $top_data = array('SELECT' => $select, 'LIMIT' => $limit);

    $sql = '{%SELECT%} ' .
           '  FROM `{%table%}`' . "\n" .
           '  WHERE UNIX_TIMESTAMP(CONCAT(`datum` , " ", `endzeit`)) >= UNIX_TIMESTAMP("{%now%}") ' . "\n" .
           '    AND `anlage_bez` = "{%place%}" ' . "\n" .
           '  ORDER BY `datum` ASC, `startzeit` ASC, `endzeit` DESC ' . "\n" .
           ' {%LIMIT%}';
    $sql = $this->sql_template($sql, $top_data, false);
    $data = array(
      'table' => MGM_STF_TABLE,
      //'today' => 'DATE()',
      'today' => MGM_TMP_TODAY,
      //'time' => 'CURTIME()',
      'time' => MGM_TMP_TIME,
      'now' => MGM_TMP_NOW,
      'place' => $place,
      'from' => $page-1,
      'num' => $num
    );
    $sql = $this->sql_template($sql, $data);
    return $this->query($sql);
  }

  public function get_places() {
    $sql = 'SELECT DISTINCT(`anlage_bez`) ' . "\n" .
           '  FROM `{%table%}`' . "\n";

    $data = array(
      'table' => MGM_STF_TABLE
    );
    $sql = $this->sql_template($sql, $data);
    $data = $this->query($sql);
    $keyval_list = array();
    foreach($data as $key => $value)
      $list[] = array('key' => $value['anlage_bez'], 'value' => $value['anlage_bez']);
    return $list;
  }

  public function get_place_name($place) {
    //@todo: change to full anlage_bez as soon as available
    $sql = 'SELECT anlage_bez ' . "\n" .
           '  FROM `{%table%}`' . "\n" .
           '  WHERE `anlage_bez` = "{%place%}" ' . "\n" .
           ' LIMIT 0,1 ';

    $data = array(
      'table' => MGM_STF_TABLE,
      'place' => $place,
    );
    $sql = $this->sql_template($sql, $data);
    $data = $this->query($sql);
    return $data[0]['anlage_bez'];
  }

  private function sql_template($string, $replace, $escape=true, $prefix = '{%', $suffix = '%}') {
    foreach($replace as $search => $replace) {
      $replace = $escape ? esc_sql($replace) : $replace;
      $string = str_replace($prefix . $search . $suffix, $replace, $string);
    }
    return $string;
  }

  public function generate_timelist($list) {
    $html = '<ul class="timelist">' . "\n";
    foreach($list as $entry) {
      $matches = array();
      preg_match("/(\d{2}:\d{2})(:\d{2})?/", $entry['startzeit'], $matches);
      $start = $matches[1];
      $html .= '<li><span class="time">' . $start . '</span><span class="event">' . $entry['label'] . '</span></li>' . "\n";
    }
    $html .= '</ul>' . "\n";
    return $html;
  }

  private function query($sql) {
    global $wpdb;
    $result = $wpdb->get_results($sql, ARRAY_A);

    return $result;
  }

}