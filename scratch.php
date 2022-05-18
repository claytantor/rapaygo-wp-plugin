


6Ds$wMK7#mZW@ARwDt
^2C@EToS8!*DM0^3su


 function rapaygo_add_menu() {
     add_submenu_page("options-general.php", "Rapaygo Plugin", "Rapaygo Plugin", "manage_options", "rapaygo-wp-cart", "rapaygo_landing_page");
 }

 add_action("admin_menu", "rapaygo_add_menu");


 function rapaygo_landing_page()
 {
     ?>
     <div class="wrap">
         <h1>
             Bitcoin Lightning Network Commerce By <a
                 href="https:rapaygo.com" target="_blank">rapaygo</a>
         </h1>
         <p>
             Welcome. This is the settings page for the Rapaygo plugin. To use the 
             plugin you will need to <a href="https:rapaygo.com/signup">create a Rapaygo account</a>. Use your account to enable the plugin and start accepting Bitcoin payments on the 
             Lightning Network.
         </p>
    
         <form method="post" action="options.php">
         <?php
         settings_fields("rapaygo_user_section");
         do_settings_sections("rapaygo-wp-cart");
         display_login_state();
         submit_button();
         ?>
         </form>
     </div>
    
     <?php
 }
    
 function rapaygo_wp_plugin_settings() {
     section name, display name, callback to print description of section, page to which section is attached.
     add_settings_section("rapaygo_user_section", "User", null, "rapaygo-wp-cart");


     setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
     last field section is optional.
     add_settings_field("username", "Username", "display_username_option", "rapaygo-wp-cart", "rapaygo_user_section");
     add_settings_field("password", "Password", "display_password_option", "rapaygo-wp-cart", "rapaygo_user_section");

      add_settings_field("rapaygo-wp-cart-username", "Username", "rapaygo_wp_plugin_options", "rapaygo-wp-cart", "rapaygo_wp_plugin_config");
     register_setting("rapaygo_wp_plugin_config", "rapaygo-wp-cart-username");
    
      add_settings_field("rapaygo-wp-cart-password", "Password", "rapaygo_wp_plugin_options", "rapaygo-wp-cart", "rapaygo_wp_plugin_config");
      register_setting("rapaygo_wp_plugin_config", "rapaygo-wp-cart-password");

     section name, form element name, callback for sanitization
     register_setting("rapaygo_user_section", "username");
     register_setting("rapaygo_user_section", "password");
 }
 add_action("admin_init", "rapaygo_wp_plugin_settings");
    
 function display_username_option() {
     ?>
     <div class="postbox" style="width: 65%; padding: 30px;">
         <input type="text" name="username"
             value="<?php
         echo stripslashes_deep(esc_attr(get_option('username'))); ?>" />
         Your rapaygo username<br />
     </div>
     <?php
 }

 function display_login_state() {
     $username = get_option('username');
     $password = get_option('password');

     $curl = curl_init();

     curl_setopt_array($curl, array(
         CURLOPT_URL => 'http:172.17.0.1:5020/auth/access_token',
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS =>'{
         "username": "claytantor@gmail.com",
         "pass_phrase": "nasty nice fold",
         "type": "wallet_owner"
     }',
         CURLOPT_HTTPHEADER => array(
         'Content-Type: application/json'
         ),
     ));
    
     $response = curl_exec($curl);
     echo '<pre>'.print_r( $response, true ).'</pre>';
    
     curl_close($curl);
     echo $response;


     ?>
     <div>
         <?php echo $response; ?>
         <?php if ($response) { ?>
             <div class="postbox" style="width: 65%; padding: 30px;">
                 <h2>
                     Login Status
                 </h2>
                 <p>
                     You are logged in as <?php echo $response; ?>
                 </p>
             </div>  
             <?php } ?>
     <?php

 }

 function display_password_option() {
     ?>
     <div class="postbox" style="width: 65%; padding: 30px;">
         <input type="password" name="password"
             value="<?php
         echo stripslashes_deep(esc_attr(get_option('password'))); ?>" />
         Your rapaygo password<br />
     </div>

     <?php
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

        update_option('cart_paypal_email', sanitize_email($_POST["cart_paypal_email"]));
        update_option('addToCartButtonName', sanitize_text_field($_POST["addToCartButtonName"]));
        update_option('rapaygo_cart_title', sanitize_text_field($_POST["rapaygo_cart_title"]));
        update_option('rapaygo_cart_empty_text', sanitize_text_field($_POST["rapaygo_cart_empty_text"]));
        update_option('cart_return_from_paypal_url', sanitize_text_field($_POST["cart_return_from_paypal_url"]));
        update_option('cart_cancel_from_paypal_url', sanitize_text_field($_POST["cart_cancel_from_paypal_url"]));
        update_option('cart_products_page_url', sanitize_text_field($_POST["cart_products_page_url"]));

        update_option('rapaygo_auto_redirect_to_checkout_page', (isset($_POST['rapaygo_auto_redirect_to_checkout_page']) && $_POST['rapaygo_auto_redirect_to_checkout_page']!='') ? 'checked="checked"':'' );
        update_option('cart_checkout_page_url', sanitize_text_field($_POST["cart_checkout_page_url"]));
        update_option('rapaygo_open_pp_checkout_in_new_tab', (isset($_POST['rapaygo_open_pp_checkout_in_new_tab']) && $_POST['rapaygo_open_pp_checkout_in_new_tab']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_reset_after_redirection_to_return_page', (isset($_POST['rapaygo_reset_after_redirection_to_return_page']) && $_POST['rapaygo_reset_after_redirection_to_return_page']!='') ? 'checked="checked"':'' );

        update_option('rapaygo_image_hide', (isset($_POST['rapaygo_image_hide']) && $_POST['rapaygo_image_hide']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_cart_paypal_co_page_style', sanitize_text_field($_POST["rapaygo_cart_paypal_co_page_style"]));
        update_option('rapaygo_strict_email_check', (isset($_POST['rapaygo_strict_email_check']) && $_POST['rapaygo_strict_email_check']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_disable_nonce_add_cart', (isset($_POST['rapaygo_disable_nonce_add_cart']) && $_POST['rapaygo_disable_nonce_add_cart']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_disable_price_check_add_cart', (isset($_POST['rapaygo_disable_price_check_add_cart']) && $_POST['rapaygo_disable_price_check_add_cart']!='') ? 'checked="checked"':'' );
        update_option('wp_use_aff_platform', (isset($_POST['wp_use_aff_platform']) && $_POST['wp_use_aff_platform']!='') ? 'checked="checked"':'' );

        update_option('rapaygo_enable_sandbox', (isset($_POST['rapaygo_enable_sandbox']) && $_POST['rapaygo_enable_sandbox']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_enable_debug', (isset($_POST['rapaygo_enable_debug']) && $_POST['rapaygo_enable_debug']!='') ? 'checked="checked"':'' );

        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.(__("Options Updated!", "rapaygo-wp-plugin")).'</strong></p></div>';
    }

    $defaultCurrency = get_option('cart_payment_currency');
    if (empty($defaultCurrency)) $defaultCurrency = __("USD", "rapaygo-wp-plugin");

    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = __("$", "rapaygo-wp-plugin");

    $baseShipping = get_option('cart_base_shipping_cost');
    if (empty($baseShipping)) $baseShipping = 0;

    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');

    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');

    $return_url =  get_option('cart_return_from_paypal_url');
    $cancel_url = get_option('cart_cancel_from_paypal_url');
    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = __("Add to Cart", "rapaygo-wp-plugin");

    $title = get_option('rapaygo_cart_title');
    $emptyCartText = get_option('rapaygo_cart_empty_text');
    $cart_products_page_url = get_option('cart_products_page_url');
    $cart_checkout_page_url = get_option('cart_checkout_page_url');

    if (get_option('rapaygo_auto_redirect_to_checkout_page'))
        $rapaygo_auto_redirect_to_checkout_page = 'checked="checked"';
    else
        $rapaygo_auto_redirect_to_checkout_page = '';

    if (get_option('rapaygo_open_pp_checkout_in_new_tab'))
        $rapaygo_open_pp_checkout_in_new_tab = 'checked="checked"';
    else
        $rapaygo_open_pp_checkout_in_new_tab = '';

    if (get_option('rapaygo_reset_after_redirection_to_return_page'))
        $rapaygo_reset_after_redirection_to_return_page = 'checked="checked"';
    else
        $rapaygo_reset_after_redirection_to_return_page = '';

    if (get_option('rapaygo_collect_address'))
        $rapaygo_collect_address = 'checked="checked"';
    else
        $rapaygo_collect_address = '';

    if (get_option('rapaygo_use_profile_shipping')){
        $rapaygo_use_profile_shipping = 'checked="checked"';
    }
    else {
        $rapaygo_use_profile_shipping = '';
    }

    if (get_option('rapaygo_image_hide')){
        $rapaygo_cart_image_hide = 'checked="checked"';
    }
    else{
        $rapaygo_cart_image_hide = '';
    }

    $rapaygo_cart_paypal_co_page_style = get_option('rapaygo_cart_paypal_co_page_style');

    $rapaygo_strict_email_check = '';
    if (get_option('rapaygo_strict_email_check')){
        $rapaygo_strict_email_check = 'checked="checked"';
    }

    $rapaygo_disable_nonce_add_cart = '';
    if (get_option('rapaygo_disable_nonce_add_cart')){
        $rapaygo_disable_nonce_add_cart = 'checked="checked"';
    }

    $rapaygo_disable_price_check_add_cart = '';
    if (get_option('rapaygo_disable_price_check_add_cart')){
        $rapaygo_disable_price_check_add_cart = 'checked="checked"';
    }

    if (get_option('wp_use_aff_platform')){
        $wp_use_aff_platform = 'checked="checked"';
    }
    else{
        $wp_use_aff_platform = '';
    }

	//$rapaygo_enable_sandbox = get_option('rapaygo_enable_sandbox');
    if (get_option('rapaygo_enable_sandbox'))
        $rapaygo_enable_sandbox = 'checked="checked"';
    else
        $rapaygo_enable_sandbox = '';

    $rapaygo_enable_debug = '';
    if (get_option('rapaygo_enable_debug')){
        $rapaygo_enable_debug = 'checked="checked"';
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

    <form method="post" action="">
    <?php wp_nonce_field('rapaygo_cart_settings_update'); ?>
    <input type="hidden" name="info_update" id="info_update" value="true" />
<?php
echo '
	<div class="postbox">
	<h3 class="hndle"><label for="title">'.(__("PayPal and Shopping Cart Settings", "rapaygo-wp-plugin")).'</label></h3>
	<div class="inside">';

echo '
<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Paypal Email Address", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_paypal_email" value="'.esc_attr($defaultEmail).'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Shopping Cart title", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="rapaygo_cart_title" value="'.esc_attr($title).'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Text/Image to Show When Cart Empty", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="rapaygo_cart_empty_text" value="'.esc_attr($emptyCartText).'" size="100" /><br />'.(__("You can either enter plain text or the URL of an image that you want to show when the shopping cart is empty", "rapaygo-wp-plugin")).'</td>
</tr>';

?>
<tr valign="top">
    <th scope="row"><?php _e("Currency", "rapaygo-wp-plugin"); ?></th>
    <td>
        <select id="cart_payment_currency" name="cart_payment_currency">
            <option value="USD" <?php echo ($defaultCurrency == 'USD') ? 'selected="selected"' : ''; ?>>US Dollars (USD)</option>
            <option value="EUR" <?php echo ($defaultCurrency == 'EUR') ? 'selected="selected"' : ''; ?>>Euros (EUR)</option>
            <option value="GBP" <?php echo ($defaultCurrency == 'GBP') ? 'selected="selected"' : ''; ?>>Pounds Sterling (GBP)</option>
            <option value="AUD" <?php echo ($defaultCurrency == 'AUD') ? 'selected="selected"' : ''; ?>>Australian Dollars (AUD)</option>
            <option value="BRL" <?php echo ($defaultCurrency == 'BRL') ? 'selected="selected"' : ''; ?>>Brazilian Real (BRL)</option>
            <option value="CAD" <?php echo ($defaultCurrency == 'CAD') ? 'selected="selected"' : ''; ?>>Canadian Dollars (CAD)</option>
            <option value="CNY" <?php echo ($defaultCurrency == 'CNY') ? 'selected="selected"' : ''; ?>>Chinese Yuan (CNY)</option>
            <option value="CZK" <?php echo ($defaultCurrency == 'CZK') ? 'selected="selected"' : ''; ?>>Czech Koruna (CZK)</option>
            <option value="DKK" <?php echo ($defaultCurrency == 'DKK') ? 'selected="selected"' : ''; ?>>Danish Krone (DKK)</option>
            <option value="HKD" <?php echo ($defaultCurrency == 'HKD') ? 'selected="selected"' : ''; ?>>Hong Kong Dollar (HKD)</option>
            <option value="HUF" <?php echo ($defaultCurrency == 'HUF') ? 'selected="selected"' : ''; ?>>Hungarian Forint (HUF)</option>
            <option value="INR" <?php echo ($defaultCurrency == 'INR') ? 'selected="selected"' : ''; ?>>Indian Rupee (INR)</option>
            <option value="IDR" <?php echo ($defaultCurrency == 'IDR') ? 'selected="selected"' : ''; ?>>Indonesia Rupiah (IDR)</option>
            <option value="ILS" <?php echo ($defaultCurrency == 'ILS') ? 'selected="selected"' : ''; ?>>Israeli Shekel (ILS)</option>
            <option value="JPY" <?php echo ($defaultCurrency == 'JPY') ? 'selected="selected"' : ''; ?>>Japanese Yen (JPY)</option>
            <option value="MYR" <?php echo ($defaultCurrency == 'MYR') ? 'selected="selected"' : ''; ?>>Malaysian Ringgits (MYR)</option>
            <option value="MXN" <?php echo ($defaultCurrency == 'MXN') ? 'selected="selected"' : ''; ?>>Mexican Peso (MXN)</option>
            <option value="NZD" <?php echo ($defaultCurrency == 'NZD') ? 'selected="selected"' : ''; ?>>New Zealand Dollar (NZD)</option>
            <option value="NOK" <?php echo ($defaultCurrency == 'NOK') ? 'selected="selected"' : ''; ?>>Norwegian Krone (NOK)</option>
            <option value="PHP" <?php echo ($defaultCurrency == 'PHP') ? 'selected="selected"' : ''; ?>>Philippine Pesos (PHP)</option>
            <option value="PLN" <?php echo ($defaultCurrency == 'PLN') ? 'selected="selected"' : ''; ?>>Polish Zloty (PLN)</option>
            <option value="SGD" <?php echo ($defaultCurrency == 'SGD') ? 'selected="selected"' : ''; ?>>Singapore Dollar (SGD)</option>
            <option value="ZAR" <?php echo ($defaultCurrency == 'ZAR') ? 'selected="selected"' : ''; ?>>South African Rand (ZAR)</option>
            <option value="KRW" <?php echo ($defaultCurrency == 'KRW') ? 'selected="selected"' : ''; ?>>South Korean Won (KRW)</option>
            <option value="SEK" <?php echo ($defaultCurrency == 'SEK') ? 'selected="selected"' : ''; ?>>Swedish Krona (SEK)</option>
            <option value="CHF" <?php echo ($defaultCurrency == 'CHF') ? 'selected="selected"' : ''; ?>>Swiss Franc (CHF)</option>
            <option value="TWD" <?php echo ($defaultCurrency == 'TWD') ? 'selected="selected"' : ''; ?>>Taiwan New Dollars (TWD)</option>
            <option value="THB" <?php echo ($defaultCurrency == 'THB') ? 'selected="selected"' : ''; ?>>Thai Baht (THB)</option>
            <option value="TRY" <?php echo ($defaultCurrency == 'TRY') ? 'selected="selected"' : ''; ?>>Turkish Lira (TRY)</option>
            <option value="VND" <?php echo ($defaultCurrency == 'VND') ? 'selected="selected"' : ''; ?>>Vietnamese Dong (VND)</option>
            <option value="RUB" <?php echo ($defaultCurrency == 'RUB') ? 'selected="selected"' : ''; ?>>Russian Ruble (RUB)</option>
        </select>
    </td>
</tr>
<?php

echo '<tr valign="top">
<th scope="row">'.(__("Currency Symbol", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_currency_symbol" value="'.esc_attr($defaultSymbol).'" size="3" style="width: 2em;" /> ('.(__("e.g.", "rapaygo-wp-plugin")).' $, &#163;, &#8364;)
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Base Shipping Cost", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_base_shipping_cost" value="'.esc_attr($baseShipping).'" size="5" /> <br />'.(__("This is the base shipping cost that will be added to the total of individual products shipping cost. Put 0 if you do not want to charge shipping cost or use base shipping cost.", "rapaygo-wp-plugin")).' <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=297" target="_blank">'.(__("Learn More on Shipping Calculation", "rapaygo-wp-plugin")).'</a></td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Free Shipping for Orders Over", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_free_shipping_threshold" value="'.esc_attr($cart_free_shipping_threshold).'" size="5" /> <br />'.(__("When a customer orders more than this amount he/she will get free shipping. Leave empty if you do not want to use it.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Must Collect Shipping Address on PayPal", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_collect_address" value="1" '.$rapaygo_collect_address.' /><br />'.(__("If checked the customer will be forced to enter a shipping address on PayPal when checking out.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Use PayPal Profile Based Shipping", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_use_profile_shipping" value="1" '.$rapaygo_use_profile_shipping.' /><br />'.(__("Check this if you want to use", "rapaygo-wp-plugin")).' <a href="https://www.tipsandtricks-hq.com/setup-paypal-profile-based-shipping-5865" target="_blank">'.(__("PayPal profile based shipping", "rapaygo-wp-plugin")).'</a>. '.(__("Using this will ignore any other shipping options that you have specified in this plugin.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Add to Cart button text or Image", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="addToCartButtonName" value="'.esc_attr($addcart).'" size="100" />
<br />'.(__("To use a customized image as the button simply enter the URL of the image file.", "rapaygo-wp-plugin")).' '.(__("e.g.", "rapaygo-wp-plugin")).' http://www.your-domain.com/wp-content/plugins/wordpress-paypal-shopping-cart/images/buy_now_button.png
<br />You can download nice add to cart button images from <a href="https://www.tipsandtricks-hq.com/ecommerce/add-to-cart-button-images-for-shopping-cart-631" target="_blank">this page</a>.
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Return URL", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.esc_attr($return_url).'" size="100" /><br />'.(__("This is the URL the customer will be redirected to after a successful payment", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Cancel URL", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_cancel_from_paypal_url" value="'.esc_attr($cancel_url).'" size="100" /><br />'.(__("The customer will be sent to the above page if the cancel link is clicked on the PayPal checkout page.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Products Page URL", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="cart_products_page_url" value="'.esc_attr($cart_products_page_url).'" size="100" /><br />'.(__("This is the URL of your products page if you have any. If used, the shopping cart widget will display a link to this page when cart is empty", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Automatic redirection to checkout page", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_auto_redirect_to_checkout_page" value="1" '.$rapaygo_auto_redirect_to_checkout_page.' />
 '.(__("Checkout Page URL", "rapaygo-wp-plugin")).': <input type="text" name="cart_checkout_page_url" value="'.$cart_checkout_page_url.'" size="60" />
<br />'.(__("If checked the visitor will be redirected to the Checkout page after a product is added to the cart. You must enter a URL in the Checkout Page URL field for this to work.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Open PayPal Checkout Page in a New Tab", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_open_pp_checkout_in_new_tab" value="1" '.$rapaygo_open_pp_checkout_in_new_tab.' />
<br />'.(__("If checked the PayPal checkout page will be opened in a new tab/window when the user clicks the checkout button.", "rapaygo-wp-plugin")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Reset Cart After Redirection to Return Page", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_reset_after_redirection_to_return_page" value="1" '.$rapaygo_reset_after_redirection_to_return_page.' />
<br />'.(__("If checked the shopping cart will be reset when the customer lands on the return URL (Thank You) page.", "rapaygo-wp-plugin")).'</td>
</tr>
</table>


<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Hide Shopping Cart Image", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_image_hide" value="1" '.$rapaygo_cart_image_hide.' /><br />'.(__("If ticked the shopping cart image will not be shown.", "rapaygo-wp-plugin")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Custom Checkout Page Logo Image", "rapaygo-wp-plugin")).'</th>
<td><input type="text" name="rapaygo_cart_paypal_co_page_style" value="'.esc_attr($rapaygo_cart_paypal_co_page_style).'" size="100" />
<br />'.(__("Specify an image URL if you want to customize the paypal checkout page with a custom logo/image. The image URL must be a 'https' URL otherwise PayPal will ignore it.", "rapaygo-wp-plugin")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use Strict PayPal Email Address Checking", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_strict_email_check" value="1" '.$rapaygo_strict_email_check.' /><br />'.(__("If checked the script will check to make sure that the PayPal email address specified is the same as the account where the payment was deposited (Usage of PayPal Email Alias will fail too).", "rapaygo-wp-plugin")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Disable Nonce Check for Add to Cart", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_disable_nonce_add_cart" value="1" '.$rapaygo_disable_nonce_add_cart.' />
<br />'.(__("Check this option if you are using a caching solution on your site. This will bypass the nonce check on the add to cart buttons.", "rapaygo-wp-plugin")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Disable Price Check for Add to Cart", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="rapaygo_disable_price_check_add_cart" value="1" '.$rapaygo_disable_price_check_add_cart.' />
<br />'.(__("Using complex characters for the product name can trigger the error: The price field may have been tampered. Security check failed. This option will stop that check and remove the error.", "rapaygo-wp-plugin")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Customize the Note to Seller Text", "rapaygo-wp-plugin")).'</th>
<td>'.(__("PayPal has removed this feature. We have created an addon so you can still collect instructions from customers at the time of checking out. ", "rapaygo-wp-plugin"))
. '<a href="https://www.tipsandtricks-hq.com/ecommerce/wp-simple-cart-collect-customer-input-in-the-shopping-cart-4396" target="_blank">'.__("View the addon details", "rapaygo-wp-plugin").'</a>'.'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use WP Affiliate Platform", "rapaygo-wp-plugin")).'</th>
<td><input type="checkbox" name="wp_use_aff_platform" value="1" '.$wp_use_aff_platform.' />
<br />'.(__("Check this if using with the", "rapaygo-wp-plugin")).' <a href="https://www.tipsandtricks-hq.com/wordpress-affiliate-platform-plugin-simple-affiliate-program-for-wordpress-blogsite-1474" target="_blank">WP Affiliate Platform plugin</a>. '.(__("This plugin lets you run your own affiliate campaign/program and allows you to reward (pay commission) your affiliates for referred sales", "rapaygo-wp-plugin")).'</td>
</tr>
</table>
</div></div>

<div class="postbox">
    <h3 class="hndle"><label for="title">'.(__("Testing and Debugging Settings", "rapaygo-wp-plugin")).'</label></h3>
    <div class="inside">

    <table class="form-table">

    <tr valign="top">
    <th scope="row">'.(__("Enable Debug", "rapaygo-wp-plugin")).'</th>
    <td><input type="checkbox" name="rapaygo_enable_debug" value="1" '.$rapaygo_enable_debug.' />
    <br />'.(__("If checked, debug output will be written to the log file. This is useful for troubleshooting post payment failures", "rapaygo-wp-plugin")).'
        <p><i>You can check the debug log file by clicking on the link below (The log file can be viewed using any text editor):</i>
        <ul>
            <li><a href="'.RAPAYGO_CART_URL.'/ipn_handle_debug.txt" target="_blank">ipn_handle_debug.txt</a></li>
        </ul>
        </p>
        <input type="submit" name="rapaygo_reset_logfile" class="button" style="font-weight:bold; color:red" value="Reset Debug Log file"/>
        <p class="description">It will reset the debug log file and timestamp it with a log file reset message.</a>
    </td></tr>

    <tr valign="top">
    <th scope="row">'.(__("Enable Sandbox Testing", "rapaygo-wp-plugin")).'</th>
    <td><input type="checkbox" name="rapaygo_enable_sandbox" value="1" '.$rapaygo_enable_sandbox.' />
    <br />'.(__("Check this option if you want to do PayPal sandbox testing. You will need to create a PayPal sandbox account from PayPal Developer site", "rapaygo-wp-plugin")).'</td>
    </tr>

    </table>

    </div>
</div>

    <div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="'.(__("Update Options &raquo;", "rapaygo-wp-plugin")).'" />
    </div>
 </form>
 ';
    echo (__("Like the Simple WordPress Shopping Cart Plugin?", "rapaygo-wp-plugin")).' <a href="https://wordpress.org/support/plugin/rapaygo-wp-plugin/reviews/?filter=5" target="_blank">'.(__("Give it a good rating", "rapaygo-wp-plugin")).'</a>';
    _e ( ". It will help us keep the plugin free & maintained.", "rapaygo-wp-plugin" );
    rapaygo_settings_menu_footer();