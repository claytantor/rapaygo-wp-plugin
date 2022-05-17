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
        update_option('addToCartButtonName', sanitize_text_field($_POST["addToCartButtonName"]));
        update_option('rapaygo_cart_title', sanitize_text_field($_POST["rapaygo_cart_title"]));
        update_option('rapaygo_cart_empty_text', sanitize_text_field($_POST["rapaygo_cart_empty_text"]));

        $defaultCurrency = get_option('cart_payment_currency');
        if (empty($defaultCurrency)) $defaultCurrency = __("USD", "rapaygo-wp-plugin");
    
        $defaultSymbol = get_option('cart_currency_symbol');
        if (empty($defaultSymbol)) $defaultSymbol = __("$", "rapaygo-wp-plugin");
    
        $baseShipping = get_option('cart_base_shipping_cost');
        if (empty($baseShipping)) $baseShipping = 0;
    
        $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');

        $addcart = get_option('addToCartButtonName');
        if (empty($addcart)) $addcart = __("Add to Cart", "rapaygo-wp-plugin");

        $title = get_option('rapaygo_cart_title');
        $emptyCartText = get_option('rapaygo_cart_empty_text');
        $cart_products_page_url = get_option('cart_products_page_url');
        $cart_checkout_page_url = get_option('cart_checkout_page_url');

    }
    ?>

    <div class="rapaygo_yellow_box">
        <p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "rapaygo-wp-plugin"); ?><br />
        <a href="https://www.tipsandtricks-hq.com/rapaygo-wp-plugin-plugin-768" target="_blank"><?php _e("Rapaygo Cart Plugin Homepage", "rapaygo-wp-plugin"); ?></a></p>
    </div>

    <div class="postbox">
        <h3 class="hndle"><label for="title"><?php _e("Quick Usage Guide", "rapaygo-wp-plugin"); ?></label></h3>
        <div class="inside">

            <p><strong><?php _e("Step 1) ","rapaygo-wp-plugin"); ?></strong><?php _e("To add an 'Add to Cart' button for a product simply add the shortcode", "rapaygo-wp-plugin"); ?> [rapaygo_cart_button name="<?php _e("PRODUCT-NAME", "rapaygo-wp-plugin"); ?>" price="<?php _e("PRODUCT-PRICE", "rapaygo-wp-plugin"); ?>"] <?php _e("to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price of your product.", "rapaygo-wp-plugin"); ?></p>
            <p><?php _e("Example add to cart button shortcode usage:", "rapaygo-wp-plugin");?> <p style="background-color: #DDDDDD; padding: 5px; display: inline;">[rapaygo_cart_button name="Test Product" price="29.95"]</p></p>
        <p><strong><?php _e("Step 2) ","rapaygo-wp-plugin"); ?></strong><?php _e("To add the shopping cart to a post or page (example: a checkout page) simply add the shortcode", "rapaygo-wp-plugin"); ?> [show_rapaygo_shopping_cart] <?php _e("to a post or page or use the sidebar widget to add the shopping cart to the sidebar.", "rapaygo-wp-plugin"); ?></p>
            <p><?php _e("Example shopping cart shortcode usage:", "rapaygo-wp-plugin");?> <p style="background-color: #DDDDDD; padding: 5px; display: inline;">[show_rapaygo_shopping_cart]</p></p>
        </div>
    </div>

<?php
}
