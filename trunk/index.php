<?php

/**
 * @link              http://TBD.com
 * @since             1.0.0
 * @package           Makae_GM
 *
 * @wordpress-plugin
 * Plugin Name:       Makae Google Maps STF2015
 * Plugin URI:        http://TBD.com/MAKAE_GM/
 * Description:       The Makae Google Maps extension for timetable markers
 * Version:           1.0.0
 * Author:            Martin Käser
 * Author URI:        http://TBD.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mgm-stf
 */

include_once 'config.php';
include_once 'classes/class.core.php';

$inst = Makae_GM_STF::instance();