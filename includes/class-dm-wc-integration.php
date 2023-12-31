<?php
namespace DMWCIntegration;

defined('ABSPATH') || exit;

class DMWCIntegration {

    /**
     * Hook into the required WooCommerce areas. 
     * woocommerce_product_options_general integrates the link download.
     * woocommerce_process_product_meta saves the download meta field.
     * woocommece_account_downloads_endpoint hooks into the existing download tab to output our content.
     */

    public function init() {
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_download_monitor_field']);
        add_action('woocommerce_process_product_meta', [$this, 'save_download_monitor_field']);
        add_action('woocommerce_account_downloads_endpoint', [$this, 'show_download_monitor_content']);
    }

    /**
     * Hook in and add the download monitor field to link the download. 
     * Works with all WooCommerce product types.
     * Make the field a Select2 to enhance the user experience to search downloads.
     */

     public function add_download_monitor_field() {
        global $post;
        echo '<div class="options_group">';
        woocommerce_wp_select([
            'id' => '_download_monitor_id',
            'label' => 'Link Download',
            'options' => array('' => 'Select Download') + $this->get_download_monitor_options(),
            'class' => 'wc-enhanced-select', 
            'style' => 'width: 400px;'
        ]);
        echo '</div>';
        // Inline JavaScript to initialize Select2
        echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $(".wc-enhanced-select").select2();
            });
        </script>';
    }
    

    /**
     * Save the download monitor field and update the associated meta with the correct ID.
     */

    public function save_download_monitor_field($post_id) {
        $download_monitor_id = $_POST['_download_monitor_id'];
        if (!empty($download_monitor_id)) {
            update_post_meta($post_id, '_download_monitor_id', esc_attr($download_monitor_id));
        }
    }

    /**
     * Hook in and add the download monitor tab
     */

    public function add_download_monitor_tab($items) {
        $items['download_monitor'] = 'Download Monitor';
        return $items;
    }

    public function add_download_monitor_endpoint() {
        add_rewrite_endpoint('download_monitor', EP_ROOT | EP_PAGES);
    }

    /**
     * Show the download monitor content in the my account area. 
     * Make sure they have purchased the download. 
     * Block the download access if they haven't purchased the content.
     */

     public function show_download_monitor_content() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            echo "You must be logged in to view downloads.";
            return;
        }
    
        // Fetch all orders for the current user
        $orders = wc_get_orders(['customer' => $user_id]);
        $download_ids = [];
    
        foreach ($orders as $order) {
            // Check if the order is completed
            if ($order->get_status() !== 'completed') {
                continue;
            }
    
            $items = $order->get_items();
            foreach ($items as $item) {
                $product_id = $item->get_product_id();
                $download_id = get_post_meta($product_id, '_download_monitor_id', true);
                if ($download_id) {
                    $download_ids[] = $download_id;
                }
            }
        }
    
        if (empty($download_ids)) {
            echo "<h2>Download Monitor Downloads</h2>";
            echo "No downloads available.";
            return;
        }
    
        echo "<h2>Download Monitor Downloads</h2>";
        echo "<ul>";
        foreach ($download_ids as $download_id) {
            $download = get_post($download_id);
            if ($download) {
                echo "<li><a href='" . esc_url(get_permalink($download_id)) . "'>" . esc_html($download->post_title) . "</a></li>";
            }
        }
        echo "</ul>";
    }
    

    private function get_download_monitor_options() {
        // Fetch Download Monitor downloads. 
        $downloads = get_posts([
            'post_type' => 'dlm_download',
            'numberposts' => -1
        ]);

        $options = [];
        foreach ($downloads as $download) {
            $options[$download->ID] = $download->post_title;
        }

        return $options;
    }
}
