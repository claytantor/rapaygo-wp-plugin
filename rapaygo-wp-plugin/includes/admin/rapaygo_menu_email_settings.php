<?php

function show_rapaygo_cart_email_settings_page()
{
    if(!current_user_can('manage_options')){
        wp_die('You do not have permission to access the settings page.');
    }

    if (isset($_POST['rapaygo_email_settings_update']))
    {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce($nonce, 'rapaygo_email_settings_update')){
                wp_die('Error! Nonce Security Check Failed! Go back to email settings menu and save the settings again.');
        }
        update_option('rapaygo_send_buyer_email', (isset($_POST['rapaygo_send_buyer_email']) && $_POST['rapaygo_send_buyer_email']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_buyer_from_email', stripslashes($_POST["rapaygo_buyer_from_email"]));
        update_option('rapaygo_buyer_email_subj', stripslashes(sanitize_text_field($_POST["rapaygo_buyer_email_subj"])));
        update_option('rapaygo_buyer_email_body', stripslashes(wp_filter_post_kses($_POST["rapaygo_buyer_email_body"])));

        update_option('rapaygo_send_seller_email', (isset($_POST['rapaygo_send_seller_email']) && $_POST['rapaygo_send_seller_email']!='') ? 'checked="checked"':'' );
        update_option('rapaygo_notify_email_address', stripslashes(sanitize_text_field($_POST["rapaygo_notify_email_address"])));
        update_option('rapaygo_seller_email_subj', stripslashes(sanitize_text_field($_POST["rapaygo_seller_email_subj"])));
        update_option('rapaygo_seller_email_body', stripslashes(wp_filter_post_kses($_POST["rapaygo_seller_email_body"])));

        echo '<div id="message" class="updated fade"><p><strong>';
        echo 'Email Settings Updated!';
        echo '</strong></p></div>';
    }
    $rapaygo_send_buyer_email = '';
    if (get_option('rapaygo_send_buyer_email')){
        $rapaygo_send_buyer_email = 'checked="checked"';
    }
    $rapaygo_buyer_from_email = get_option('rapaygo_buyer_from_email');
    $rapaygo_buyer_email_subj = get_option('rapaygo_buyer_email_subj');
    $rapaygo_buyer_email_body = get_option('rapaygo_buyer_email_body');
    $rapaygo_send_seller_email = '';
    if (get_option('rapaygo_send_seller_email')){
        $rapaygo_send_seller_email = 'checked="checked"';
    }
    $rapaygo_notify_email_address = get_option('rapaygo_notify_email_address');
    if(empty($rapaygo_notify_email_address)){
        $rapaygo_notify_email_address = get_bloginfo('admin_email'); //default value
    }
    $rapaygo_seller_email_subj = get_option('rapaygo_seller_email_subj');
    if(empty($rapaygo_seller_email_subj)){
        $rapaygo_seller_email_subj = "Notification of product sale";
    }
    $rapaygo_seller_email_body = get_option('rapaygo_seller_email_body');
    if(empty($rapaygo_seller_email_body)){
        $rapaygo_seller_email_body = "Dear Seller\n".
        "\nThis mail is to notify you of a product sale.\n".
        "\n{product_details}".
        "\n\nThe sale was made to {first_name} {last_name} ({payer_email})".
        "\n\nThanks";
    }
    ?>

    <div class="rapaygo_yellow_box">
    <p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "rapaygo-wp-plugin"); ?><br />
    <a href="https://www.tipsandtricks-hq.com/rapaygo-wp-plugin-plugin-768" target="_blank"><?php _e("Rapaygo Cart Plugin Homepage", "rapaygo-wp-plugin"); ?></a></p>
    </div>

    <form method="post" action="">
    <?php wp_nonce_field('rapaygo_email_settings_update'); ?>
    <input type="hidden" name="info_update" id="info_update" value="true" />

    <div class="postbox">
    <h3 class="hndle"><label for="title"><?php _e("Purchase Confirmation Email Settings", "rapaygo-wp-plugin");?></label></h3>
    <div class="inside">

    <p><i><?php _e("The following options affect the emails that gets sent to your buyers after a purchase.", "rapaygo-wp-plugin");?></i></p>

    <table class="form-table">

    <tr valign="top">
    <th scope="row"><?php _e("Send Emails to Buyer After Purchase", "rapaygo-wp-plugin");?></th>
    <td><input type="checkbox" name="rapaygo_send_buyer_email" value="1" <?php echo $rapaygo_send_buyer_email; ?> /><span class="description"><?php _e("If checked the plugin will send an email to the buyer with the sale details. If digital goods are purchased then the email will contain the download links for the purchased products.", "rapaygo-wp-plugin");?></a></span></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("From Email Address", "rapaygo-wp-plugin");?></th>
    <td><input type="text" name="rapaygo_buyer_from_email" value="<?php echo esc_attr($rapaygo_buyer_from_email); ?>" size="50" />
    <br /><p class="description"><?php _e("Example: Your Name &lt;sales@your-domain.com&gt; This is the email address that will be used to send the email to the buyer. This name and email address will appear in the from field of the email.", "rapaygo-wp-plugin");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Buyer Email Subject", "rapaygo-wp-plugin");?></th>
    <td><input type="text" name="rapaygo_buyer_email_subj" value="<?php echo esc_attr($rapaygo_buyer_email_subj); ?>" size="50" />
    <br /><p class="description"><?php _e("This is the subject of the email that will be sent to the buyer.", "rapaygo-wp-plugin");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Buyer Email Body", "rapaygo-wp-plugin");?></th>
    <td>
    <textarea name="rapaygo_buyer_email_body" cols="90" rows="7"><?php echo esc_textarea($rapaygo_buyer_email_body); ?></textarea>
    <br /><p class="description"><?php _e("This is the body of the email that will be sent to the buyer. Do not change the text within the braces {}. You can use the following email tags in this email body field:", "rapaygo-wp-plugin");?>
    <br />{first_name} – <?php _e("First name of the buyer", "rapaygo-wp-plugin");?>
    <br />{last_name} – <?php _e("Last name of the buyer", "rapaygo-wp-plugin");?>
    <br />{payer_email} – <?php _e("Email Address of the buyer", "rapaygo-wp-plugin");?>
    <br />{address} – <?php _e("Address of the buyer", "rapaygo-wp-plugin");?>
    <br />{product_details} – <?php _e("The item details of the purchased product (this will include the download link for digital items).", "rapaygo-wp-plugin");?>
    <br />{transaction_id} – <?php _e("The unique transaction ID of the purchase", "rapaygo-wp-plugin");?>
    <br />{order_id} – <?php _e("The order ID reference of this transaction in the cart orders menu", "rapaygo-wp-plugin");?>
    <br />{purchase_amt} – <?php _e("The amount paid for the current transaction", "rapaygo-wp-plugin");?>
    <br />{purchase_date} – <?php _e("The date of the purchase", "rapaygo-wp-plugin");?>
    <br />{coupon_code} – <?php _e("Coupon code applied to the purchase", "rapaygo-wp-plugin");?>
    </p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Send Emails to Seller After Purchase", "rapaygo-wp-plugin");?></th>
    <td><input type="checkbox" name="rapaygo_send_seller_email" value="1" <?php echo $rapaygo_send_seller_email; ?> /><span class="description"><?php _e("If checked the plugin will send an email to the seller with the sale details", "rapaygo-wp-plugin");?></a></span></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Notification Email Address*", "rapaygo-wp-plugin");?></th>
    <td><input type="text" name="rapaygo_notify_email_address" value="<?php echo esc_attr($rapaygo_notify_email_address); ?>" size="50" />
    <br /><p class="description"><?php _e("This is the email address where the seller will be notified of product sales. You can put multiple email addresses separated by comma (,) in the above field to send the notification to multiple email addresses.", "rapaygo-wp-plugin");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Seller Email Subject*", "rapaygo-wp-plugin");?></th>
    <td><input type="text" name="rapaygo_seller_email_subj" value="<?php echo esc_attr($rapaygo_seller_email_subj); ?>" size="50" />
    <br /><p class="description"><?php _e("This is the subject of the email that will be sent to the seller for record.", "rapaygo-wp-plugin");?></p></td>
    </tr>

    <tr valign="top">
    <th scope="row"><?php _e("Seller Email Body*", "rapaygo-wp-plugin");?></th>
    <td>
    <textarea name="rapaygo_seller_email_body" cols="90" rows="7"><?php echo esc_textarea($rapaygo_seller_email_body); ?></textarea>
    <br /><p class="description"><?php _e("This is the body of the email that will be sent to the seller for record. Do not change the text within the braces {}. You can use the following email tags in this email body field:", "rapaygo-wp-plugin");?>
    <br />{first_name} – <?php _e("First name of the buyer", "rapaygo-wp-plugin");?>
    <br />{last_name} – <?php _e("Last name of the buyer", "rapaygo-wp-plugin");?>
    <br />{payer_email} – <?php _e("Email Address of the buyer", "rapaygo-wp-plugin");?>
    <br />{address} – <?php _e("Address of the buyer", "rapaygo-wp-plugin");?>
    <br />{product_details} – <?php _e("The item details of the purchased product (this will include the download link for digital items).", "rapaygo-wp-plugin");?>
    <br />{transaction_id} – <?php _e("The unique transaction ID of the purchase", "rapaygo-wp-plugin");?>
    <br />{order_id} – <?php _e("The order ID reference of this transaction in the cart orders menu", "rapaygo-wp-plugin");?>
    <br />{purchase_amt} – <?php _e("The amount paid for the current transaction", "rapaygo-wp-plugin");?>
    <br />{purchase_date} – <?php _e("The date of the purchase", "rapaygo-wp-plugin");?>
    <br />{coupon_code} – <?php _e("Coupon code applied to the purchase", "rapaygo-wp-plugin");?>
    </p></td>
    </tr>

    </table>

    </div></div>

    <div class="submit">
        <input type="submit" class="button-primary" name="rapaygo_email_settings_update" value="<?php echo (__("Update Options &raquo;", "rapaygo-wp-plugin")) ?>" />
    </div>
    </form>

    <?php
    rapaygo_settings_menu_footer();
}
