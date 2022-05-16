<?php

/*
 * General settings menu page
 */
function rapaygo_show_general_settings_page ()
{
    if(!current_user_can('manage_options')){
        wp_die('You do not have permission to access this settings page.');
    }

    if(isset($_POST['rapaygo_reset_logfile'])) {
        // Reset the debug log file
        if(rapaygo_reset_logfile()){
            echo '<div id="message" class="updated fade"><p><strong>Debug log file has been reset!</strong></p></div>';
        }
        else{
            echo '<div id="message" class="updated fade"><p><strong>Debug log file could not be reset!</strong></p></div>';
        }
    }

    if (isset($_POST['info_update']))
    {
    	$nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce($nonce, 'rapaygo_cart_settings_update')){
                wp_die('Error! Nonce Security Check Failed! Go back to settings menu and save the settings again.');
        }

        $currency_code = sanitize_text_field($_POST["cart_payment_currency"]);
        $currency_code = trim(strtoupper($currency_code));//Currency code must be uppercase.
        update_option('cart_payment_currency', $currency_code);
        update_option('cart_currency_symbol', sanitize_text_field($_POST["cart_currency_symbol"]));
        update_option('cart_base_shipping_cost', sanitize_text_field($_POST["cart_base_shipping_cost"]));
        update_option('cart_free_shipping_threshold', sanitize_text_field($_POST["cart_free_shipping_threshold"]));
        update_option('rapaygo_collect_address', (isset($_POST['rapaygo_collect_address']) && $_POST['rapaygo_collect_address']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_use_profile_shipping', (isset($_POST['rapaygo_use_profile_shipping']) && $_POST['rapaygo_use_profile_shipping']!='') ? 'checked="checked"':'' );


    }



}
