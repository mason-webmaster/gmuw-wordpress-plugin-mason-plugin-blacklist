<?php

/**
 * Main plugin file for the Mason plugin blacklist plugin
 */

/**
 * Plugin Name:       Mason WordPress: Plugin blacklist
 * Author:            Mason Web Administration
 * Plugin URI:        https://github.com/mason-webmaster/gmuw-wordpress-plugin-mason-plugin-blacklist
 * Description:       Mason WordPress Plugin to prevent certain plugins from being installed
 * Version:           0.9
 */


// Exit if this file is not called directly.
if (!defined('WPINC')) {
	die;
}

// define constants
	// define list of disallowed plugins. You could define a different list in the wp-config file.
	if ( ! defined( 'GMUW_DISALLOWED_PLUGINS' ) ) {
		define( 'GMUW_DISALLOWED_PLUGINS', [
			'elementor',
			'elementor-pro'
		] );
	}
	// define notification email. You could define a different value for this constant in the wp-config file.
	if ( ! defined( 'GMUW_DISALLOWED_PLUGINS_NOTIFICATION_EMAIL' ) ) {
		define( 'GMUW_DISALLOWED_PLUGINS_NOTIFICATION_EMAIL', "webmaster@gmu.edu" );
	}

// filter the plugins_api_result to remove listings of plugins matching those on the blacklist
add_filter( 'plugins_api_result', 'gmuw_plugin_api_filter', 10, 3 );
function gmuw_plugin_api_filter( $res, $action, $arg ) {

	if ( ! property_exists( $res, "plugins" ) ) {
		return $res;
	}

	foreach ( $res->plugins as $i => $plugin ) {
		if ( in_array( $plugin['slug'], GMUW_DISALLOWED_PLUGINS ) ) {
			unset( $res->plugins[ $i ] );
		}
	}

	return $res;
}

// prevent activation of plugins listed in the blacklist
add_action( 'activate_plugin', 'gmuw_prevent_activation', 1, 2 );
function gmuw_prevent_activation( $plugin, $network_wide ) {
	$plug_slug = explode( '/', $plugin );
	if ( in_array( $plug_slug[0], GMUW_DISALLOWED_PLUGINS ) ) {
		throw new \Exception();
	}
}

// prevent people from installing plugins if they are on the list
add_filter( 'user_has_cap', 'gmuw_prevent_install_plugins_cap', 10, 4 );
function gmuw_prevent_install_plugins_cap( $allcaps, $caps, $args, $user ) {
	if ( ! in_array( 'install_plugins', $caps ) ) {
		return $allcaps;
	}

	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	$plugin = isset( $_REQUEST['slug'] ) ? trim( $_REQUEST['slug'] ) : '';
	if ( in_array( $plugin, GMUW_DISALLOWED_PLUGINS ) ) {
		$allcaps['install_plugins'] = 0;

		$username  = $user->data->user_nicename;
		$useremail = $user->data->user_email;
		$site      = site_url();
		$to        = GMUW_DISALLOWED_PLUGINS_NOTIFICATION_EMAIL;
		$subject   = 'Plugin Blacklist Triggered';
		$message   = "The user $username ($useremail) at $site attempted to install the $plugin, which has been blacklisted ";
		wp_mail( $to, $subject, $message );
	}

	return $allcaps;
}
