<?php
define('MGM_STF_URL', plugin_dir_url(__FILE__));
define('MGM_STF_DIR', plugin_dir_path(__FILE__));
define('MGM_STF_TABLE', 'mgm_timetable');
define('MGM_TMP_TODAY', '2015-06-20');
define('MGM_TMP_TIME', '09' . date(':i:s', time()));

define('MGM_TMP_NOW', '2015-06-20 09' . date(':i:s', time()));
define('MGM_PLACE_MAPPING', serialize(array(
  'wurf' => array(
    'label' => 'Würfe',
    'disciplines' => array('SB', 'SPE')
  ),
  'kugel' => array(
    'label' => 'Kugelstossen',
    'disciplines' => array('KUG')
  ),
  'sprint' => array(
    'label' => 'Sprint 100m / Pendelstaffette',
    'disciplines' => array('PS80')
  ),
  'fachtest' => array(
    'label' => 'Fachtest Allround',
    'disciplines' => array('FTA')
  ),
  'nat. turn' => array(
    'label' => 'Nationalturnen',
    'disciplines' => array('STS', 'STH')
  ),
  'rundbahn' => array(
    'label' => 'Rundbahn (400 / 800)',
    'disciplines' => array()
  ),
  'fit & fun' => array(
    'label' => 'Fit und Fun',
    'disciplines' =>  array('FF1','FF2','FF3')
  ),
  'gymnastikfelder' => array(
    'label' => 'Bühne & Gymnastikfelder',
    'disciplines' => array('GYG', 'TAE', 'GYK')
  ),
  'turnhalle' => array(
    'label' => 'Turnhalle (EGT, RE, FTU)',
    'disciplines' => array('EGT', 'RE', 'FTU')
  ),
  'geraetezelt' => array(
    'label' => 'Gerätezelt',
    'disciplines' => array('BA', 'BO', 'GK', 'SP', 'SR', 'SSB')
  ),
  'weitsprung' => array(
    'label' => 'Weitsprung',
    'disciplines' => array('WE')
  ),
  'hochsprung' => array(
    'label' => 'Hochsprung',
    'disciplines' => array('HO')
  )
  // ????????????????
  // array('WE', 'WE2'),
  // array('WE', 'WE1'),
)));