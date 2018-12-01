<?php
/*
Plugin Name: SanMD
Plugin URI: 插件的介绍或更新地址
Description: 插件描述
Version: 插件版本，例如 1.0
Author: 插件作者名称
Author URI: 插件作者的链接
License: A "Slug" license name e.g. GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit;
date_default_timezone_set('PRC');

global $wpdb,$SanMDVersion;

$SanMDVersion = "0.1.0";
$wpdb->sanmd = $wpdb->prefix."sanmd";

define("sanmd",plugin_dir_url( __FILE__ ));
define('SANMD_URL', plugins_url('', __FILE__)."/");
define('SANMD_PATH', dirname( __FILE__ ));
define('SANMD_SRC', dirname( __FILE__ ).'/statics/');

function sanmd_install_action() {
	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$create_sanmessage_sql = "CREATE TABLE IF NOT EXISTS $wpdb->sanmd (".
                             "id int(10) unsigned NOT NULL AUTO_INCREMENT,".
	                         "stat int(1) NOT NULL DEFAULT '0',".
                             "uid int(10) NOT NULL,".
                             "title varchar(255) NOT NULL,".
	                         "content mediumtext NOT NULL,".
	                         "time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,".
	                         "PRIMARY KEY (id)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	dbDelta( $create_sanmessage_sql );
}


function sanmd_install() {
	// trigger our function that registers the custom post type
	sanmd_install_action();

	// clear the permalinks after the post type has been registered
	flush_rewrite_rules();
}

function sanmd_uninstall_action(){

}

function sanmd_uninstall() {
	// unregister the post type, so the rules are no longer in memory
	sanmd_uninstall_action();
	// clear the permalinks to remove our post type's rules from the database
	flush_rewrite_rules();
}




register_deactivation_hook( __FILE__, 'sanmd_uninstall' );
register_activation_hook( __FILE__, 'sanmd_install' );
require_once SANMD_PATH . '/includes/Core.php';