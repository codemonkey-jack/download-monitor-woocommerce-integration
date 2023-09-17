<?php
namespace DMWCIntegration;

defined('ABSPATH') || exit;

class DMWCIntegration {

    /**
     * Hook into the required WooCommerce areas. 
     * woocommerce_product_options_general integrates the link download.
     * woocommerce_process_product_meta saves the download meta field.
     * woocommece_account_menu_items adds the new download monitor menu item to the my account area on the front-end. 
     */

    public function init() {
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_download_monitor_field']);
        add_action('woocommerce_process_product_meta', [$this, 'save_download_monitor_field']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_download_monitor_tab']);
        add_action('init', [$this, 'add_download_monitor_endpoint']);
        add_action('woocommerce_account_download_monitor_endpoint', [$this, 'show_download_monitor_content']);
    }

    /**
     * Hook in and add the download monitor field to link the download. 
     * Works with all WooCommerce product types.
     */

    public function add_download_monitor_field() {
        global $post;
        echo '<div class="options_group">';
        woocommerce_wp_select([
            'id' => '_download_monitor_id',
            'label' => 'Link Download',
            'options' => $this->get_download_monitor_options()
        ]);
        echo '</div>';
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
            echo "No downloads available.";
            return;
        }
    
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
