<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function cep_delete_plugin() {
	global $wpdb;
	
	// Only one option is set by this plugin
	delete_option( 'widget_excerpt-widget-plus' );
}

cep_delete_plugin();

?>