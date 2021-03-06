<?php
// Exit if accessed directly
if (false === defined('ABSPATH')) {
    exit;
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
            $this->title              = sanitize_text_field( $this->get_option('title') );
            $this->description        = sanitize_textarea_field( $this->get_option('description') );
            $this->order_states       = $this->rapaygo_sanitize_array($this->get_option('order_states'));
            $this->debug              = 'yes' === $this->get_option('debug', 'no');

			// define rapaygo settings
			$this->api_key               = sanitize_text_field( $this->get_option('api_key') );
			$this->api_secret            = sanitize_text_field( $this->get_option('api_secret') );

            $this->api_url               = sanitize_url( $this->get_option('api_url') );
            $this->rapaygo_app_url       = sanitize_url( $this->get_option('rapaygo_app_url') );
            $this->success_redirect      = sanitize_url( $this->get_option('success_redirect') );

            $this->store_logo_url      = sanitize_url( $this->get_option('store_logo_url') );
            

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
            

            $this->is_initialized = true;
        }

        public function rapaygo_sanitize_array($array) {
            foreach ( $array as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = $this->rapaygo_sanitize_array($value);
                }
                else {
                    $value = sanitize_text_field( $value );
                }
            }
        
            return $array;
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
                $actualMethod = sanitize_text_field( get_post_meta( $order->get_id(), '_payment_method', true ) );
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

                'api_url' => array(
                    'title'       => __('API Url', 'rapaygo-for-woocommerce'),
                    'type'        => 'text',
                    'description' => __('Rapaygo API Endpoint.', 'rapaygo-for-woocommerce'),
                    'default'     => 'https://api.rapaygo.com/v1',
                    'desc_tip'    => true,
                ),
                'rapaygo_app_url' => array(
                        'title'       => __('Application Url', 'rapaygo-for-woocommerce'),
                        'type'        => 'text',
                        'description' => __('Rapaygo API Endpoint.', 'rapaygo-for-woocommerce'),
                        'default'     => 'https://rapaygo.com',
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
                    'description' => __('The page (URL suffix without site url prefix) you want successful payments to be direct to from rapaygo.com', 'rapaygo-for-woocommerce'),
                    'default'     => '',
                    'desc_tip'    => true,
               ),
                'store_logo_url' => array(
                    'title'       => __('Store Logo Url', 'rapaygo-for-woocommerce'),
                    'type'        => 'text',
                    'description' => __('The site media link (URL suffix without site url prefix) for the invoice to display.', 'rapaygo-for-woocommerce'),
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
                    'description' => sprintf(__('This plugin version is %s and your PHP version is %s. If you need assistance, you can access our support portal at https://rapaygo.freshdesk.com/support/home. Thank you for using Rapaygo!', 'rapaygo-for-woocommerce'), constant("RAPAYGO_VERSION"), PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION),
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
                            <th><?php echo esc_html( $bp_name ); ?></th>
                            <td>
                                <select name="woocommerce_rapaygo_order_states[<?php echo esc_attr( $bp_state ); ?>]">
                                <?php

                                $order_states = get_option('woocommerce_rapaygo_settings');
                                $order_states = $order_states['order_states'];
                                foreach ($wc_statuses as $wc_state => $wc_name) {
                                    $current_option = sanitize_text_field( $order_states[$bp_state] );

                                    if (true === empty($current_option)) {
                                        $current_option = sanitize_text_field( $df_statuses[$bp_state] );
                                    }

                                    if ($current_option === $wc_state) {
                                        echo "<option value=\"".esc_attr( $wc_state )."\" selected>".esc_html( $wc_name )."</option>\n";
                                    } else {
                                        echo "<option value=\"".esc_attr( $wc_state )."\">".esc_html( $wc_name )."</option>\n";
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
                    $bp_state = sanitize_text_field( $bp_state );
                    if (false === isset($_POST['woocommerce_rapaygo_order_states'][ $bp_state ])) {
                        continue;
                    }

                    $wc_state = sanitize_text_field( $_POST['woocommerce_rapaygo_order_states'][ $bp_state ] );

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
                $order_states = sanitize_text_field( $_POST[ $order_states_key ] );
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
                     $url = sanitize_url( $_POST[ $this->plugin_id . $this->id . '_' . $key ] );
                 } else {
                     $url = '';
                 }
             }
             return $url;
        }

        public function get_rapaygo_redirect($order_id, $order, $order_price, $currency, $payment_hash)
        {
            $this->log('    [Info] Entered get_rapaygo_redirect with order_id =  ' . $order_id);

            // hard coded to thank you page
            $site_success = site_url().$this->success_redirect;
            $raypago_success_redirect_encoded = urlencode_deep(  $site_success );

            $store_logo_url_encoded =  urlencode_deep(site_url().$this->store_logo_url);

            //get the order total and currency

            //create the redirect to rapaygo
            $redirect = $this->rapaygo_app_url . '/invoice_payment/pay/' .  $payment_hash . 
                '?success=' . $raypago_success_redirect_encoded . 
                '&order_id=' . $order_id . 
                '&amount_fiat=' . $order_price . 
                '&currency=' . $currency .
                '&store_logo_url=' . $store_logo_url_encoded ;
                

            return $redirect;
        }

        public function get_access_token($api_key, $api_secret){

            $auth_url = $this->api_url . '/auth/key';
            $this->log('[Info] attempting to get credentials for api key = ' . $api_key . ' at url:' . $auth_url);


            $array_with_parameters = array(
                    'key'      => $api_key,
                    'secret'   => $api_secret
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

            $currency = get_woocommerce_currency();

            $invoice_request = array(
                'amount_fiat'       => $order_price,
                'currency'          => $currency,
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

                $rapaygo_redirect = $this->get_rapaygo_redirect($order_id, $order, $order_price, $currency, $invoice_info['payment_hash']);
                
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