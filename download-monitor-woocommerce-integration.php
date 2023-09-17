<?php
/**
 * Plugin Name: Download Monitor & WooCommerce Integration
 * Description: Integrates Download Monitor with WooCommerce.
 * Version: 1.0
 * Author: Download Monitor
 */

namespace DMWCIntegration;

defined('ABSPATH') || exit;

/**
 * Check Download Monitor is installed and activated during admin_init. 
 * If not activated display a WordPress admin notice and deactivate the integration.
 */
add_action('admin_init', function() {
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('download-monitor/download-monitor.php')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>This plugin requires <a href="https://wordpress.org/plugins/download-monitor/" target="_blank">Download Monitor</a> to function.</p></div>';
        });

        deactivate_plugins(plugin_basename(__FILE__));
    }
});

require_once plugin_dir_path(__FILE__) . 'includes/class-dm-wc-integration.php';

$dm_wc_integration = new \DMWCIntegration\DMWCIntegration();
$dm_wc_integration->init();
