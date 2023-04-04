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
			// elementor
			'elementor',
			'elementor-pro',
			'elementor-beta',
			// elementor addons
			'essential-addons-for-elementor-lite',
			'premium-addons-for-elementor',
			'royal-elementor-addons',
			'header-footer-elementor',
			'happy-elementor-addons',
			'elementskit-lite',
			'jeg-elementor-kit',
			'unlimited-elements-for-elementor',
			'ele-custom-skin',
			'qi-addons-for-elementor',
			'sticky-header-effects-for-elementor',
			'visibility-logic-elementor',
			'addon-elements-for-elementor-page-builder',
			'portfolio-elementor',
			'connect-polylang-elementor',
			'addons-for-elementor',
			'metform',
			'anywhere-elementor',
			'powerpack-lite-for-elementor',
			'ooohboi-steroids-for-elementor',
			'music-player-for-elementor',
			'exclusive-addons-for-elementor',
			'rife-elementor-extensions',
			'scroll-magic-addon-for-elementor',
			'the-plus-addons-for-elementor-page-builder',
			'piotnet-addons-for-elementor',
			'custom-icons-for-elementor',
			'timeline-widget-addon-for-elementor',
			'skyboot-custom-icons-for-elementor'
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

		// send notification email
		$site      = site_url();
		$to        = GMUW_DISALLOWED_PLUGINS_NOTIFICATION_EMAIL;
		$subject   = "Plugin Activation Blocked: $site / $plugin";
		$message   = "Someone at $site attempted to activate the plugin: $plugin, which is on the blacklist.";
		wp_mail( $to, $subject, $message );

		// throw exception
		throw new \Exception();

	}
}
