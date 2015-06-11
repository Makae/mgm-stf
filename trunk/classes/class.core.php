<?php

class Makae_GM_STF {
  private static $instance;
  private $mapping;
  private function __construct() {
    $this->mapping = unserialize(MGM_PLACE_MAPPING);
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
    //$num = array_key_exists('num', $_REQUEST) ? $_REQUEST['num'] : 5;
    $num = 15;
    $page = array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1;

    if(is_null($place))
      throw new Exception("No suitable Place found");

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
    $num = count($list);
    $html = '<h5>' . $title . '</h5>';

    if($num == 0)
      return $html . '<p>' . MGM_STF_TEXT_NONE_FOUND . '</p>';

    $list = $this->generate_timelist($list);
    $html .= $list;
    return $html;
  }

  public function get_timetable($place, $num=5, $page=1, $count=false) {
    if($count) {
      $select = 'SELECT COUNT(*) ' . "\n";
      $limit = '';
    } else {
      $select = 'SELECT CONCAT(`prefix`, " ", `ort`, " (", riegen_nr, ")") as label, startzeit, endzeit, datum ' . "\n";
      $limit = 'LIMIT {%from%}, {%num%}';
    }
    $top_data = array('SELECT' => $select, 'LIMIT' => $limit);
    $disciplines = $this->mapping[$place]['disciplines'];

    $sql = '{%SELECT%} ' .
           '  FROM `{%table%}`' . "\n" .
           '  WHERE UNIX_TIMESTAMP(CONCAT(`datum` , " ", `endzeit`)) >= UNIX_TIMESTAMP("{%now%}") ' . "\n" .
           '    AND {%conditions%} ' . "\n" .
           '  ORDER BY `datum` ASC, `startzeit` ASC, `endzeit` DESC ' . "\n" .
           ' {%LIMIT%}';
    $sql = $this->sql_template($sql, $top_data, false);
    $data = array(
      'table' => MGM_STF_TABLE,
      'today' => 'DATE()',
      'now' => 'CURTIME()',
      //'today' => MGM_TMP_TODAY,
      //'now' => MGM_TMP_NOW,
      'place' => $place,
      'from' => $page-1,
      'num' => $num
    );
    $sql = $this->sql_template($sql, $data);
    $sql = $this->sql_template($sql, array('conditions' => $this->orSql('disz_kurz', $disciplines)), false);

    return $this->query($sql);
  }

  private function orSql($column, $array) {
    array_walk($array, 'esc_sql');
    if(count($array) > 0 && is_string($array[0])) {
      $glue = '\', \'';
      $left = '\'';
      $right = '\'';
    } else {
      $glue = ', ';
      $left = '';
      $right = '';
    }
    $set = $left . implode($glue, $array) . $right;
    return " $column IN ($set)";
  }

  public function get_places() {
    foreach($this->mapping as $key => $entry)
      $list[] = array('key' => $key, 'value' => $entry['label']);
    return $list;
  }

  public function get_place_name($place) {
    $places = $this->get_places();

    foreach($places as $value)
      if($value['key'] == $place)
        return $value['value'];
    return null;
  }

  // public function get_places() {
  //   $sql = 'SELECT DISTINCT(`anlage_bez`) ' . "\n" .
  //          '  FROM `{%table%}`' . "\n";

  //   $data = array(
  //     'table' => MGM_STF_TABLE
  //   );
  //   $sql = $this->sql_template($sql, $data);
  //   $data = $this->query($sql);
  //   $keyval_list = array();
  //   foreach($data as $key => $value)
  //     $list[] = array('key' => $value['anlage_bez'], 'value' => $value['anlage_bez']);
  //   return $list;
  // }

  // public function get_place_name($place) {
  //   //@todo: change to full anlage_bez as soon as available
  //   $sql = 'SELECT anlage_bez ' . "\n" .
  //          '  FROM `{%table%}`' . "\n" .
  //          '  WHERE `anlage_bez` = "{%place%}" ' . "\n" .
  //          ' LIMIT 0,1 ';

  //   $data = array(
  //     'table' => MGM_STF_TABLE,
  //     'place' => $place,
  //   );
  //   $sql = $this->sql_template($sql, $data);
  //   $data = $this->query($sql);
  //   return $data[0]['anlage_bez'];
  // }

  private function sql_template($string, $replace, $escape=true, $prefix = '{%', $suffix = '%}') {
    foreach($replace as $search => $replace) {
      $replace = $escape ? esc_sql($replace) : $replace;
      $string = str_replace($prefix . $search . $suffix, $replace, $string);
    }
    return $string;
  }

  public function generate_timelist($list) {
    $html = '<ul class="timelist">' . "\n";
    $previous_date = false;
    foreach($list as $entry) {
      if($previous_date != $entry['datum']) {
        $html .= '<li class="mgm-stf-date"><strong>' . $this->translate_date($entry['datum']) . '</strong></li>';
        $previous_date = $entry['datum'];
      }
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

  private function translate_date($date) {
    $trans = array(
        'Monday'    => 'Montag',
        'Tuesday'   => 'Dienstag',
        'Wednesday' => 'Mittwoch',
        'Thursday'  => 'Donnerstag',
        'Friday'    => 'Freitag',
        'Saturday'  => 'Samstag',
        'Sunday'    => 'Sonntag',
        'Mon'       => 'Mo',
        'Tue'       => 'Di',
        'Wed'       => 'Mi',
        'Thu'       => 'Do',
        'Fri'       => 'Fr',
        'Sat'       => 'Sa',
        'Sun'       => 'So',
        'January'   => 'Januar',
        'February'  => 'Februar',
        'March'     => 'März',
        'May'       => 'Mai',
        'June'      => 'Juni',
        'July'      => 'Juli',
        'October'   => 'Oktober',
        'December'  => 'Dezember'
    );
    $strdate = date('l, d. F Y', strtotime($date));
    return strtr($strdate, $trans);
  }

}