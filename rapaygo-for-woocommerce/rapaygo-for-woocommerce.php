<?php

/*
    Plugin Name: Rapaygo for WooCommerce 
    Plugin URI:  https://github.com/claytantor/rapaygo-wp-plugin/rapaygo-for-woocommerce
    Description: Enable your WooCommerce store to accept Bitcoin with Rapaygo.
    Author:      Rapaygo LLC
    Author URI:  https://rapaygo.com

    Version:           3.0.16
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

define("RAPAYGO_VERSION", "1.0.16");
define( 'RAPAYGO_SITE_URL', site_url() );

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

add_filter('woocommerce_payment_gateways', 'woogatewaypro_add_gateway_class');
function woogatewaypro_add_gateway_class($gateways){
    $gateways[] = 'WC_Gateway_Rapaygo';
    return $gateways;
}

// Ensures WooCommerce is loaded before initializing the BitPay plugin
add_action('plugins_loaded', 'woocommerce_rapaygo_init', 0);
add_action('plugins_loaded', 'rapaygo_load_textdomain');
add_action('admin_notices', 'fx_admin_notice_show_migration_message' );

register_activation_hook(__FILE__, 'woocommerce_rapaygo_activate');

function woocommerce_rapaygo_init()
{
    if (true === class_exists('WC_Gateway_Rapaygo')) {
        return;
    }

    if (false === class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Gateway_Rapaygo extends WC_Payment_Gateway
    {
        private $is_initialized = false;

        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {
            // General
            $this->id                 = 'rapaygo';
            $this->icon               = plugin_dir_url(__FILE__).'assets/img/icon.png';
            $this->has_fields         = false;
            $this->order_button_text  = __('Proceed to Rapaygo', 'rapaygo-for-woocommerce');
            $this->method_title       = 'Rapaygo';
            $this->method_description = 'Rapaygo allows you to accept bitcoin payments on your WooCommerce store.';

            // Load the settings.
            $this->init_form_fields();

            $this->init_settings();
            // Define user set variables
            $this->title              = $this->get_option('title');
            $this->description        = $this->get_option('description');
            $this->order_states       = $this->get_option('order_states');
            $this->debug              = 'yes' === $this->get_option('debug', 'no');

			// define rapaygo settings
			$this->api_key               = $this->get_option('api_key');
			$this->api_secret            = $this->get_option('api_secret');

            $this->api_url               = 'http://172.17.0.1:5020';
            $this->rapaygo_app_url       = 'http://localhost:3000';
            $this->success_redirect      = $this->get_option('success_redirect');
            

            // Define debugging & informational settings
            $this->debug_php_version    = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
            $this->debug_plugin_version = constant("RAPAYGO_VERSION");

            $this->log('Rapaygo Woocommerce payment plugin object constructor called. Plugin is v' . $this->debug_plugin_version . ' and server is PHP v' . $this->debug_php_version);
            $this->log('    [Info] $this->api_key            = ' . $this->api_key);
            $this->log('    [Info] $this->api_secret         = ' . $this->api_secret);
            $this->log('    [Info] $this->api_url        = ' . $this->api_url);


            $this->transaction_speed  = $this->get_option('transaction_speed');
            $this->log('    [Info] Transaction speed is now set to: ' . $this->transaction_speed);

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_order_states'));

            // Valid for use and IPN Callback
            if (false === $this->is_valid_for_use()) {
                $this->enabled = 'no';
                $this->log('    [Info] The plugin is NOT valid for use!');
            } else {
                $this->enabled = 'yes';
                $this->log('    [Info] The plugin is ok to use.');
                // add_action('woocommerce_api_wc_gateway_rapaygo', array($this, 'rapaygo_callback_handler'));
                add_action('woocommerce_api_'.strtolower(get_class($this)), 'rapaygo_callback_handler');
            }

            // enque custom scripts
            add_action('wp_enqueue_scripts', array($this, 'rapaygo_payment_gateway_scripts'));


            // register the callback
            // add_action('woocommerce_api_'.strtolower(get_class($this)), 'rapaygo_callback_handler');

            // Additional token initialization.
            // if (rapaygo_get_additional_tokens()) {
            //   $this->initialize_additional_tokens();
            // }

            // set the callback handler 
            

            $this->is_initialized = true;
        }

        public function rapaygo_payment_gateway_scripts() {

            // process a token only on cart/checkout pages
            if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
                return;
            }
        
            // stop enqueue JS if payment gateway is disabled
            if ( 'no' === $this->enabled ) {
                return;
            }
        
            // stop enqueue JS if API keys are not set
            if ( empty( $this->api_secret ) || empty( $this->api_key ) ) {
                return;
            }
        
            // stop enqueue JS if test mode is enabled
            if ( ! $this->test_mode ) {
                return;
            }
        
            // stop enqueue JS if site without SSL
            if ( ! is_ssl() ) {
                return;
            }
        
            // payment processor JS that allows to get a token
            // replace this with your own JS file when you get it distributed
            // wp_enqueue_script( 'ybc_js', 'https://www.example.com/api/get-token.js' );
        
            // custom JS that works with get-token.js
            wp_register_script( 'woocommerce_pay_rapaygo', plugins_url( 'assets/js/token-script.js', __FILE__ ), array( 'jquery', 'rapaygo_js' ) );
        
            // use api key and secret to get access token
            wp_localize_script( 'woocommerce_pay_rapaygo', 'rapaygo_params', array(
                'apiKey' => $this->api_key,
                'apiSecret' => $this->api_secret,
            ) );
        
            wp_enqueue_script( 'woocommerce_pay_rapaygo' );
        
        }


        public function is_rapaygo_payment_method($order)
        {
            $actualMethod = '';
            if (method_exists($order, 'get_payment_method')) {
                $actualMethod = $order->get_payment_method();
            } else {
                $actualMethod = get_post_meta( $order->get_id(), '_payment_method', true );
            }

            return (false !== strpos($actualMethod, 'rapaygo'));
        }

        public function __destruct()
        {
        }

        public function is_valid_for_use()
        {
            // Check that API credentials are set
            if (true === is_null($this->api_key) ||
                true === is_null($this->api_secret))
            {
                return false;
            }

            $this->log('    [Info] Plugin is valid for use.');

            return true;
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields()
        {
            $this->log('    [Info] Entered init_form_fields()...');
            $log_file = 'rapaygo-' . sanitize_file_name( wp_hash( 'rapaygo' ) ) . '-log';
            $logs_href = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-status&tab=logs&log_file=' . $log_file;

            $this->form_fields = array(
                'title' => array(
                    'title'       => __('Title', 'rapaygo-for-woocommerce'),
                    'type'        => 'text',
                    'description' => __('Controls the name of this payment method as displayed to the customer during checkout.', 'rapaygo-for-woocommerce'),
                    'default'     => __('Bitcoin with Lightning', 'rapaygo-for-woocommerce'),
                    'desc_tip'    => true,
               ),
               'description' => array(
                    'title'       => __('Customer Message', 'rapaygo-for-woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Message to explain how the customer will be paying for the purchase.', 'rapaygo-for-woocommerce'),
                    'default'     => 'You will be redirected to Rapaygo to complete your purchase.',
                    'desc_tip'    => true,
               ),
                'api_key' => array(
                      'title'       => __('API Key', 'rapaygo-for-woocommerce'),
                      'type'        => 'text',
                      'description' => __('Your Rapaygo API Key.', 'rapaygo-for-woocommerce'),
                      'default'     => '',
                      'desc_tip'    => true,
                ),
                'api_secret' => array(
                      'title'       => __('API Secret', 'rapaygo-for-woocommerce'),
                      'type'        => 'text',
                      'description' => __('Your Rapaygo API Secret.', 'rapaygo-for-woocommerce'),
                      'default'     => '',
                      'desc_tip'    => true,
                ),
                'success_redirect' => array(
                    'title'       => __('Success Redirect Page', 'rapaygo-for-woocommerce'),
                    'type'        => 'text',
                    'description' => __('The page you want successful payments to be direct to from rapaygo.com', 'rapaygo-for-woocommerce'),
                    'default'     => '',
                    'desc_tip'    => true,
               ),
               'debug' => array(
                    'title'       => __('Debug Log', 'rapaygo-for-woocommerce'),
                    'type'        => 'checkbox',
                    'label'       => sprintf(__('Enable logging <a href="%s" class="button">View Logs</a>', 'rapaygo-for-woocommerce'), $logs_href),
                    'default'     => 'no',
                    'description' => sprintf(__('Log Rapaygo events, such as IPN requests, inside <code>%s</code>', 'rapaygo-for-woocommerce'), wc_get_log_file_path('rapaygo')),
                    'desc_tip'    => true,
               ),
               'support_details' => array(
                    'title'       => __( 'Plugin & Support Information', 'rapaygo' ),
                    'type'        => 'title',
                    'description' => sprintf(__('This plugin version is %s and your PHP version is %s. If you need assistance, please come on our chat https://chat.rapaygoserver.org. Thank you for using Rapaygo!', 'rapaygo-for-woocommerce'), constant("RAPAYGO_VERSION"), PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION),
               ),
           );

            $this->log('    [Info] Initialized form fields: ' . var_export($this->form_fields, true));
            $this->log('    [Info] Leaving init_form_fields()...');
        }


        /**
         * HTML output for form field type `order_states`
         */
        public function generate_order_states_html()
        {
            $this->log('    [Info] Entered generate_order_states_html()...');

            ob_start();

            $bp_statuses = array(
            'new'=>'New Order',
            'paid'=>'Paid',
            'confirmed'=>'Confirmed',
            'complete'=>'Complete',
            'invalid'=>'Invalid',
            'expired'=>'Expired',
            'event_invoice_paidAfterExpiration'=>'Paid after expiration',
            'event_invoice_expiredPaidPartial' => 'Expired with partial payment');
            $df_statuses = array(
            'new'=>'wc-pending',
            'paid'=>'wc-on-hold',
            'confirmed'=>'wc-processing',
            'complete'=>'wc-processing',
            'invalid'=>'wc-failed',
            'expired'=>'wc-cancelled',
            'event_invoice_paidAfterExpiration' => 'wc-failed',
            'event_invoice_expiredPaidPartial' => 'wc-failed');

            $wc_statuses = wc_get_order_statuses();
            $wc_statuses = array('RAPAYGO_IGNORE' => '') + $wc_statuses;
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">Order States:</th>
                <td class="forminp" id="rapaygo_order_states">
                    <table cellspacing="0">
                        <?php

                            foreach ($bp_statuses as $bp_state => $bp_name) {
                            ?>
                            <tr>
                            <th><?php echo $bp_name; ?></th>
                            <td>
                                <select name="woocommerce_rapaygo_order_states[<?php echo $bp_state; ?>]">
                                <?php

                                $order_states = get_option('woocommerce_rapaygo_settings');
                                $order_states = $order_states['order_states'];
                                foreach ($wc_statuses as $wc_state => $wc_name) {
                                    $current_option = $order_states[$bp_state];

                                    if (true === empty($current_option)) {
                                        $current_option = $df_statuses[$bp_state];
                                    }

                                    if ($current_option === $wc_state) {
                                        echo "<option value=\"$wc_state\" selected>$wc_name</option>\n";
                                    } else {
                                        echo "<option value=\"$wc_state\">$wc_name</option>\n";
                                    }
                                }

                                ?>
                                </select>
                            </td>
                            </tr>
                            <?php
                        }

                        ?>
                    </table>
                </td>
            </tr>
            <?php

            $this->log('    [Info] Leaving generate_order_states_html()...');

            return ob_get_clean();
        }

        /**
         * Save order states
         */
        public function save_order_states()
        {
            $this->log('    [Info] Entered save_order_states()...');

            $bp_statuses = array(
                'new'      => 'New Order',
                'paid'      => 'Paid',
                'confirmed' => 'Confirmed',
                'complete'  => 'Complete',
                'invalid'   => 'Invalid',
                'expired'   => 'Expired',
                'event_invoice_paidAfterExpiration' => 'Paid after expiration',
                'event_invoice_expiredPaidPartial' => 'Expired with partial payment'
            );

            $wc_statuses = wc_get_order_statuses();

            if (true === isset($_POST['woocommerce_rapaygo_order_states'])) {

                $bp_settings = get_option('woocommerce_rapaygo_settings');
                $order_states = $bp_settings['order_states'];

                foreach ($bp_statuses as $bp_state => $bp_name) {
                    if (false === isset($_POST['woocommerce_rapaygo_order_states'][ $bp_state ])) {
                        continue;
                    }

                    $wc_state = $_POST['woocommerce_rapaygo_order_states'][ $bp_state ];

                    if (true === array_key_exists($wc_state, $wc_statuses)) {
                        $this->log('    [Info] Updating order state ' . $bp_state . ' to ' . $wc_state);
                        $order_states[$bp_state] = $wc_state;
                    }

                }
                $bp_settings['order_states'] = $order_states;
                update_option('woocommerce_rapaygo_settings', $bp_settings);
            }

            $this->log('    [Info] Leaving save_order_states()...');
        }

        /**
         * Validate API Token
         */
        public function validate_api_token_field()
        {
            return '';
        }

        /**
         * Validate Support Details
         */
        public function validate_support_details_field()
        {
            return '';
        }

        /**
         * Validate Order States
         */
        public function validate_order_states_field()
        {
            $order_states = $this->get_option('order_states');

            $order_states_key = $this->plugin_id . $this->id . '_order_states';
            if ( isset( $_POST[ $order_states_key ] ) ) {
                $order_states = $_POST[ $order_states_key ];
            }
            return $order_states;
        }

        /**
         * Validate Notification URL
         */
        public function validate_url_field($key)
        {
            $url = $this->get_option($key);

            if ( isset( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) ) {
                 if (filter_var($_POST[ $this->plugin_id . $this->id . '_' . $key ], FILTER_VALIDATE_URL) !== false) {
                     $url = $_POST[ $this->plugin_id . $this->id . '_' . $key ];
                 } else {
                     $url = '';
                 }
             }
             return $url;
        }

        // /**
        //  * Validate Redirect URL
        //  */
        // public function validate_redirect_url_field()
        // {
        //     $redirect_url = $this->get_option('redirect_url', '');

        //     if ( isset( $_POST['woocommerce_rapaygo_redirect_url'] ) ) {
        //          if (filter_var($_POST['woocommerce_rapaygo_redirect_url'], FILTER_VALIDATE_URL) !== false) {
        //              $redirect_url = $_POST['woocommerce_rapaygo_redirect_url'];
        //          } else {
        //              $redirect_url = '';
        //          }
        //      }
        //      return $redirect_url;
        // }

        /**
         * Output for the order received page.
         */
        // public function thankyou_page($order_id)
        // {
        //     $this->log('    [Info] Entered thankyou_page with order_id =  ' . $order_id);

        //     // Remove cart
        //     WC()->cart->empty_cart();

        //     // Intentionally blank.

        //     $this->log('    [Info] Leaving thankyou_page with order_id =  ' . $order_id);
        // }

        public function get_rapaygo_redirect($order_id, $order, $payment_hash)
        {
            $this->log('    [Info] Entered get_rapaygo_redirect with order_id =  ' . $order_id);

            // if('' == get_option('permalink_structure')){
            //     $callback = site_url().'/?wc-api=WC_Gateway_Rapaygo';
            // } else {
            //     $callback = site_url().'/wc-api/WC_Gateway_Rapaygo/';
            // }

            // $raypago_success_redirect_encoded = urlencode_deep(  $callback );

            // hard coded to thank you page
            $site_success = site_url().$this->success_redirect;
            $raypago_success_redirect_encoded = urlencode_deep(  $site_success );

            //create the redirect to rapaygo
            $redirect = $this->rapaygo_app_url . '/invoice_payment/pay/' . $payment_hash . '?success=' . $raypago_success_redirect_encoded . '&order_id=' . $order_id;


            return $redirect;
        }

        public function get_access_token($api_key, $api_secret){

            $auth_url = $this->api_url . '/auth/access_token';
            $this->log('[Info] attempting to get credentials for api key = ' . $api_key . ' at url:' . $auth_url);

            // Array with arguments for API interaction
            // $args = array(
            
            // );           
            // $auth_response = wp_remote_post( $auth_url, $args );

            $array_with_parameters = array(
                    'username'      => $api_key,
                    'pass_phrase'   => $api_secret,
                    'type'          => "wallet_owner"
            );

            $auth_response = wp_remote_post($auth_url, array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => json_encode($array_with_parameters),
                'method'      => 'POST',
                'data_format' => 'body',
            ));

            // $response = wp_remote_post( $url, array(
            //     'method'      => 'POST',
            //     'timeout'     => 45,
            //     'redirection' => 5,
            //     'httpversion' => '1.0',
            //     'blocking'    => true,
            //     'headers'     => array(),
            //     'body'        => array(
            //         'username' => 'bob',
            //         'password' => '1234xyz'
            //     ),
            //     'cookies'     => array()
            //     )
            // );

            return $auth_response;
        }

        public function process_payment( $order_id ) {

            global $woocommerce;
         
            // get order details
            if (true === empty($order_id)) {
                $this->log('    [Error] The Rapaygo payment plugin was called to process a payment but the order_id was missing.');
                throw new \Exception('The Rapaygo payment plugin was called to process a payment but the order_id was missing. Cannot continue!');
            }
            
            $order = wc_get_order( $order_id );
            
            if (false === $order) {
                $this->log('    [Error] The Rapaygo payment plugin was called to process a payment but could not retrieve the order details for order_id ' . $order_id);
                throw new \Exception('The Rapaygo payment plugin was called to process a payment but could not retrieve the order details for order_id ' . $order_id . '. Cannot continue!');
            }
            $order_number = $order->get_order_number();

         
            // now we want to send payment info to rapaygo
            $payment_process_url = $this->api_url . '/invoice_payment/fiat' ;
            $this->log('[Info] Entered process_payment() with order_id = ' . $order_id . ' at url:' . $payment_process_url);

            //must authenticate
            $auth_response = $this->get_access_token($this->api_key, $this->api_secret);

            $auth_body = array();
            if( !is_wp_error( $auth_response ) ) {
                $auth_body = json_decode( $auth_response['body'], true );
            } else {
                $this->log('[Error] cannot process_payment() with order_id = ' . $order_id  . ' at url' .$payment_process_url);
                wc_add_notice(  'There was an error when attempting to process payment gateway credentials.', 'error' );
                return;                
            }

        
            // manage order  
            $order_total = $order->calculate_totals();
            if (!empty($order_total)) {
                $order_price = (float)$order_total;
                if (!is_float($order_price)) {
                    throw new \Exception("Price must be formatted as a float ". $order_price);
                }

            } else {
                $this->log('    [Error] The Rapaygo payment plugin was called to process a payment but could not set item->setPrice to $order->calculate_totals(). The empty() check failed!');
                throw new \Exception('The Rapaygo payment plugin was called to process a payment but could not set item->setPrice to $order->calculate_totals(). The empty() check failed!');
            }    

            // order basics
            $raypago_webhook = site_url().'/?wc-api=WC_Gateway_Rapaygo';
            if('' == get_option('permalink_structure')){
                $raypago_webhook = site_url().'/?wc-api=WC_Gateway_Rapaygo';
            } else {
                $raypago_webhook = site_url().'/wc-api/WC_Gateway_Rapaygo/';
            }

            // $raypago_webhook_encoded = urlencode_deep(  $callback );

            $invoice_request = array(
                'amount_fiat'       => $order_price,
                'currency'          => get_woocommerce_currency(),
                'memo'              => 'online order id:'. $order_id . ' order number:' . $order->get_order_number(),
                'webhook'           => $raypago_webhook,
                'webhook_external_id' => $order_id
            );

            $invoice_response = wp_remote_post($payment_process_url, array(
                'headers'     => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => $auth_body['access_token']
                ),
                'body'        => json_encode($invoice_request),
                'method'      => 'POST',
                'data_format' => 'body',
            ));
   
            if( !is_wp_error( $invoice_response ) ) {

                $this->log('[Info] we have an invoice.' );
         
                $invoice_info = json_decode( $invoice_response['body'], true );
                // {
                //     "checking_id": "b4f634dbd282aebdc07c65dc0d8acfc5da5daa18d63c6541911bd0e62c075667",
                //     "invoice_id": 3018,
                //     "message": "Invoice created",
                //     "payment_hash": "b4f634dbd282aebdc07c65dc0d8acfc5da5daa18d63c6541911bd0e62c075667",
                //     "payment_request": "lnbc3340n1p38t7fgpp5knmrfk7js2htmsruvhwqmzk0chd9m2sc6c7x2sv3r0gwvtq82enshp5lhux705x7x3zwh05rezvv68c6zu9ljyew7yppkqr4q9657adgszqcqzpgxqyz5vqsp55udlhmnf5cejtnkwtkf54htlncygd74avwaum6hyecek0v6vlvyq9qyyssq5py9gs78wn84g0mxz4fn996dpl58793u65xec5uuppgzv4kdueqp0j8gntvlz7awkapef7esf97aa4ru6e0vlecrxlrsc5sk9vpky7gqstne05"
                //   }
                // this could be configured by the user
                $order->update_status('on-hold', __('PENDING lightning invoice payment '. $invoice_info['payment_hash']));

                $rapaygo_redirect = $this->get_rapaygo_redirect($order_id, $order, $invoice_info['payment_hash']);
                
                if($rapaygo_redirect)
                {
                    $this->log('[Info] Existing Rapaygo invoice has already been created, redirecting to it...');
                    $this->log('[Info] Leaving process_payment()...');
                    return array(
                        'result'   => 'success',
                        'redirect' => $rapaygo_redirect,
                    );
                } else {
                    wc_add_notice(  'Could not build redirect.', 'error' );
                    return;
                }
         
            } else {
                $this->log('[Error] problem with invoice.' );
                wc_add_notice(  'There was an error when attempting to process payment to url:' . $payment_process_url, 'error' );
                return;
            }
         
        }    

        public function log($message)
        {
            if (true === isset($this->debug) && 'yes' == $this->debug) {
                if (false === isset($this->logger) || true === empty($this->logger)) {
                    $this->logger = new WC_Logger();
                }

                $this->logger->add('rapaygo', $message);
            }
        }

    }
    /**
    * Add Rapaygo Payment Gateways to WooCommerce
    **/
    function wc_add_rapaygo($methods)
    {
        // Add main Rapaygo payment method class.
        $methods[] = 'WC_Gateway_Rapaygo';

        // Add additional tokens as separate payment methods.
        if ($additional_tokens = rapaygo_get_additional_tokens()) {
            foreach ($additional_tokens as $token) {
              $methods[] = $token['classname'];
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

    add_filter('woocommerce_payment_gateways', 'wc_add_rapaygo');

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

        if ($file == $this_plugin) {
            $log_file = 'rapaygo-' . sanitize_file_name( wp_hash( 'rapaygo' ) ) . '-log';
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_gateway_rapaygo">Settings</a>';
            $logs_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-status&tab=logs&log_file=' . $log_file . '">Logs</a>';
            array_unshift($links, $settings_link, $logs_link);
        }

        return $links;
    }

   

    function action_woocommerce_thankyou_rapaygo($order_id)
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
        echo str_replace('{$paymentStatus}', $status_desctiption, $payment_status);
    }
    add_action("woocommerce_thankyou_rapaygo", 'action_woocommerce_thankyou_rapaygo', 10, 1);
}

function woocommerce_rapaygo_failed_requirements()
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

function extractCustomnerFromUrl($url)
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
    // $this->log('[Info] CALLBACK Entered rapaygo_callback_handler()...');

    $logger = new WC_Logger();
    $logger->add('rapaygo', $message);

    $logger->add('rapaygo', 'successfully got callback: A');

    // if(!empty($_POST))
    // {
    //     // when using application/x-www-form-urlencoded or multipart/form-data as the HTTP Content-Type in the request
    //     // NOTE: if this is the case and $_POST is empty, check the variables_order in php.ini! - it must contain the letter P
    //     return $_POST;
    // }

    // $logger->add('rapaygo', 'successfully got callback: B');
    // $logger->add('rapaygo', 'successfully got callback: B2 ' . var_export($_POST, true));

    // when using application/json as the HTTP Content-Type in the request 
    $raw_post = file_get_contents('php://input');
    $logger->add('rapaygo', 'successfully got callback: RAW '. $raw_post);
    $post = json_decode($raw_post, true);
    // if(json_last_error() == JSON_ERROR_NONE)
    // {
    //     return $post;
    // }

    $logger->add('rapaygo', 'successfully got callback: C');

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

    $order_id = isset($post['webhook_external_id']) ? $post['webhook_external_id'] : null;
    $payment_hash = isset($post['payment_hash']) ? $post['payment_hash'] : null;

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


