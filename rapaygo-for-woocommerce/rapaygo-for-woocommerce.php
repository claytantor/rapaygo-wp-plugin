<?php

/*
    Plugin Name: Rapaygo for WooCommerce 
    Plugin URI:  https://github.com/claytantor/rapaygo-wp-plugin/rapaygo-for-woocommerce
    Description: Enable your WooCommerce store to accept Bitcoin with Rapaygo.
    Author:      Rapaygo LLC
    Author URI:  https://rapaygo.com

    Version:           1.0.18
    License:           Copyright 2011-2018 Rapaygo & BitPay Inc., MIT License
    License URI:       https://github.com/claytantor/rapaygo-wp-plugin/rapaygo-for-woocommerce/LICENSE
    GitHub Plugin URI: https://github.com/claytantor/rapaygo-wp-plugin/rapaygo-for-woocommerce

    Text Domain: rapaygo-for-woocommerce
    Domain Path: languages

 */


// Exit if accessed directly
if (false === defined('ABSPATH')) {
    exit;
}

define("RAPAYGO_VERSION", "1.0.18");
define( 'RAPAYGO_SITE_URL', site_url() );

// Ensures WooCommerce is loaded before initializing the BitPay plugin
add_action('plugins_loaded', 'rapaygo_woocommerce_init', 0);
add_action('plugins_loaded', 'rapaygo_load_textdomain');
add_action('admin_notices', 'rapaygo_admin_notice_show_migration_message' );

register_activation_hook(__FILE__, 'rapaygo_woocommerce_activate');

/**
 * Load translations
 */
function rapaygo_load_textdomain() {
    $slug = 'rapaygo-for-woocommerce';
    $locale = get_locale();
    $locale = apply_filters('plugin_locale', $locale, $slug);
    load_textdomain($slug, WP_LANG_DIR . '/plugins/' . $slug . '-' . $locale . '.mo' );
    load_plugin_textdomain($slug, false, dirname(plugin_basename( __FILE__ )) . '/languages/');
}

add_filter('woocommerce_payment_gateways', 'rapaygo_woocommerce_add_gateway_class');
function rapaygo_woocommerce_add_gateway_class($gateways){
    $gateways[] = 'WC_Gateway_Rapaygo';
    return $gateways;
}



function rapaygo_woocommerce_activate(){

}

function rapaygo_admin_notice_show_migration_message(){

}

function rapaygo_woocommerce_init()
{
    if (true === class_exists('WC_Gateway_Rapaygo')) {
        return;
    }

    if (false === class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once 'class-rapaygo-for-woocommerce.php';
    /**
    * Add Rapaygo Payment Gateways to WooCommerce
    **/
    function rapaygo_wc_add_payment_gateway($methods)
    {
        // Add main Rapaygo payment method class.
        $methods[] = 'WC_Gateway_Rapaygo';

        // Add additional tokens as separate payment methods.
        if ($additional_tokens = rapaygo_get_additional_tokens()) {
            foreach ($additional_tokens as $token) {
              $methods[] =  $token['classname'];
            }
        }

        return $methods;
    }

  /**
   * Check and return any configured additional tokens.
   *
   * @param string $mode
   *   Can be 'payment' or 'promotion'.
   *
   * @return array|null
   */
    function rapaygo_get_additional_tokens($mode = null)
    {
        $rapaygo_settings = get_option('woocommerce_rapaygo_settings', null);

        if (!empty($rapaygo_settings['additional_tokens'])) {
            $tokens = [];
            $tokens_data = str_getcsv($rapaygo_settings['additional_tokens'], "\n");
            foreach ($tokens_data as $row) {
			    $token_config = str_getcsv($row, ";");
			    // If mode is set, only return matching tokens.
			    if (isset($mode) && $mode !== $token_config[2]) {
				    continue;
			    }

			    // Todo: check/make sure token config is complete.
			    $token['symbol'] = sanitize_text_field($token_config[0]);
			    $token['name'] = sanitize_text_field($token_config[1]);
			    $token['mode'] = sanitize_text_field($token_config[2]);
			    $token['icon'] = sanitize_text_field($token_config[3]);
			    $token['classname'] = "WC_Gateway_Rapaygo_{$token['symbol']}";
			    $tokens[] = $token;
            }

            return !empty($tokens) ? $tokens : null;
        }

        return null;
    }

    add_filter('woocommerce_payment_gateways', 'rapaygo_wc_add_payment_gateway');

    if (!function_exists('rapaygo_log'))  {
        function rapaygo_log($message)
        {
            $logger = new WC_Logger();
            $logger->add('rapaygo', $message);
        }
    }
    /**
     * Add Settings link to the plugin entry in the plugins menu
     **/
    add_filter('plugin_action_links', 'rapaygo_plugin_action_links', 10, 2);

    function rapaygo_plugin_action_links($links, $file)
    {
        static $this_plugin;

        if (false === isset($this_plugin) || true === empty($this_plugin)) {
            $this_plugin = plugin_basename(__FILE__);
        }

        if ($file === $this_plugin) {
            $log_file = 'rapaygo-' . sanitize_file_name( wp_hash( 'rapaygo' ) ) . '-log';
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_gateway_rapaygo">Settings</a>';
            $logs_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-status&tab=logs&log_file=' . $log_file . '">Logs</a>';
            array_unshift($links, $settings_link, $logs_link);
        }

        return $links;
    }

   

    function rapaygo_action_woocommerce_thankyou($order_id)
    {
        $wc_order = wc_get_order($order_id);

        if($wc_order === false) {
            return;
        }
        $order_data     = $wc_order->get_data();
        $status         = $order_data['status'];

        $payment_status = file_get_contents(plugin_dir_path(__FILE__) . 'templates/paymentStatus.tpl');
        $payment_status = str_replace('{$statusTitle}', _x('Payment Status', 'woocommerce_rapaygo'), $payment_status);
        switch ($status)
        {
            case 'on-hold':
                $status_desctiption = _x('Waiting for payment', 'woocommerce_rapaygo');
                break;
            case 'processing':
                $status_desctiption = _x('Payment processing', 'woocommerce_rapaygo');
                break;
            case 'completed':
                $status_desctiption = _x('Payment completed', 'woocommerce_rapaygo');
                break;
            case 'failed':
                $status_desctiption = _x('Payment failed', 'woocommerce_rapaygo');
                break;
            default:
                $status_desctiption = _x(ucfirst($status), 'woocommerce_rapaygo');
                break;
        }
        echo esc_html( str_replace('{$paymentStatus}', $status_desctiption, $payment_status) );
    }
    add_action("woocommerce_thankyou_rapaygo", 'rapaygo_action_woocommerce_thankyou', 10, 1);
}

function rapaygo_woocommerce_failed_requirements()
{
    global $wp_version;
    global $woocommerce;

    $errors = array();
    if (extension_loaded('openssl')  === false){
        $errors[] = 'The Rapaygo payment plugin requires the OpenSSL extension for PHP in order to function. Please contact your web server administrator for assistance.';
    }
    // PHP 5.4+ required
    if (true === version_compare(PHP_VERSION, '5.4.0', '<')) {
        $errors[] = 'Your PHP version is too old. The Rapaygo payment plugin requires PHP 5.4 or higher to function. Please contact your web server administrator for assistance.';
    }

    // Wordpress 3.9+ required
    if (true === version_compare($wp_version, '3.9', '<')) {
        $errors[] = 'Your WordPress version is too old. The Rapaygo payment plugin requires Wordpress 3.9 or higher to function. Please contact your web server administrator for assistance.';
    }

    // WooCommerce required
    if (true === empty($woocommerce)) {
        $errors[] = 'The WooCommerce plugin for WordPress needs to be installed and activated. Please contact your web server administrator for assistance.';
    }elseif (true === version_compare($woocommerce->version, '2.2', '<')) {
        $errors[] = 'Your WooCommerce version is too old. The Rapaygo payment plugin requires WooCommerce 2.2 or higher to function. Your version is '.$woocommerce->version.'. Please contact your web server administrator for assistance.';
    }

    // GMP or BCMath required
    if (false === extension_loaded('gmp') && false === extension_loaded('bcmath')) {
        $errors[] = 'The Rapaygo payment plugin requires the GMP or BC Math extension for PHP in order to function. Please contact your web server administrator for assistance.';
    }

    // Curl required
    if (false === extension_loaded('curl')) {
        $errors[] = 'The Rapaygo payment plugin requires the Curl extension for PHP in order to function. Please contact your web server administrator for assistance.';
    }

    if (false === empty($errors)) {
        return implode("<br>\n", $errors);
    } else {
        return false;
    }

}

function rapaygo_extractCustomnerFromUrl($url)
{
    $component = parse_url($url);
    if(!$component){
        throw new \Exception('Url was invalid');
    }
    if(array_key_exists("port",$component) && isset($component["port"])){
        $port = $component["port"];
    }else  if($component["scheme"] === "http"){
        $port = 80;

    }else if($component["scheme"] === "https"){
        $port = 443;
    }
    $host = $component["host"];
    return new \Bitpay\Network\Customnet($host, $port);
}

function rapaygo_callback_handler()
{

    $logger = new WC_Logger();
    $logger->add('rapaygo', $message);

    // when using application/json as the HTTP Content-Type in the request 
    $raw_post = file_get_contents('php://input');
    $post = json_decode($raw_post, true);
  

    // {
    //     "id": 19,
    //     "checking_id": "5194bce8099af5af5f303e779f457584d7d01bb72198f13363f8fb00c1e0c164",
    //     "pending": false,
    //     "msat_amount": 830000,
    //     "amount": 830,
    //     "msat_fee": 0,
    //     "fee": 0,
    //     "msat_tx_fee": 8300,
    //     "tx_fee": 8,
    //     "msat_ln_fee": 0,
    //     "ln_fee": 0,
    //     "memo": "2a834 POS payment. online order id:60 order number:60",
    //     "status": "COMPLETED",
    //     "created_at": "2022-05-19 10:58:20.130105",
    //     "created_at_ts": 1652983100.130105,
    //     "updated_at": "2022-05-19 17:58:30.279859",
    //     "preimage": "",
    //     "payment_hash": "5194bce8099af5af5f303e779f457584d7d01bb72198f13363f8fb00c1e0c164",
    //     "payment_request": "lnbc8300n1p3gdpfupp52x2te6qfnt667hes8eme73t4sntaqxahyxv0zvmrlrasps0qc9jqhp5wyghpezt9ztnacz2z8u5eg28ev39k8nyd0gkq2pxgrmucgmevmgqcqzpgxqyz5vqsp58avv4zyscd8ed59mmrlypvjeueww9rzemffum9lamea7rk4t36qs9qyyssqxh00288a5c3ygk09993zlvlu8dtqhr2ppss227m2g6gj9trtlg24vwv5lmqwhzs9kp07ynflf3mky4uqhf44xvt6wvxx37htggkdjacqsemp8a",
    //     "extra": "",
    //     "wallet_id": 1,
    //     "webhook": "http://localhost:8000/?wc-api=WC_Gateway_Rapaygo",
    //     "webhook_status": "pending",
    //     "withdraw_voucher_id": null,
    //     "lnurl_payment_id": null
    // }
    
    $logger->add('rapaygo', 'successfully got callback: ' . var_export($post, true));

    $order_id = isset($post['webhook_external_id']) ? absint( $post['webhook_external_id'] ) : null;
    $payment_hash = isset($post['payment_hash']) ? sanitize_text_field( $post['payment_hash'] ) : null;

    // // if the backend to make sure they paid the update
    $order = wc_get_order( $order_id );
    $logger->add('rapaygo', '[Info] Entered rapaygo_callback_handler()...' . $order_id . ' payment hash:' . $payment_hash);

    // // we received the payment
    if($order){
        $order->payment_complete();
        $order->reduce_order_stock();
        $order->add_order_note( '[Info] Entered rapaygo_callback_handler...' . $order_id . ' payment hash:' . $payment_hash, false );
    
        // // notes to customer
        $order->add_order_note( '[Info] Awesome, your order is paid! Thank you!', true );   
    } else {
        $logger->add('rapaygo', '[Info] order could not be found...' . $order_id . ' payment hash:' . $payment_hash);
    }

    exit;

}


