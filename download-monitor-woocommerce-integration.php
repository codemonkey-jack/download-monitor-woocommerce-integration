<?php
/**
 * Plugin Name: Download Monitor & WooCommerce Integration
 * Description: Integrates Download Monitor with WooCommerce.
 * Version: 1.0
 * Author: Download Monitor
 */

namespace DMWCIntegration;

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-dm-wc-integration.php';

$dm_wc_integration = new \DMWCIntegration\DMWCIntegration();
$dm_wc_integration->init();
