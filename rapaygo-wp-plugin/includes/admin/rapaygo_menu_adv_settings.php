<?php

function show_rapaygo_cart_adv_settings_page() {

    require_once(RAPAYGO_CART_PATH . 'includes/admin/rapaygo_admin_utils.php');

    if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have permission to access the settings page.' );
    }

    if ( isset( $_POST[ 'rapaygo_adv_settings_update' ] ) ) {
	$nonce = $_REQUEST[ '_wpnonce' ];
	if ( ! wp_verify_nonce( $nonce, 'rapaygo_adv_settings_update' ) ) {
	    wp_die( 'Error! Nonce Security Check Failed! Go back to email settings menu and save the settings again.' );
	}

	$enable_pp_smart_checkout	 = filter_input( INPUT_POST, 'rapaygo_enable_pp_smart_checkout', FILTER_SANITIZE_NUMBER_INT );
	$live_client_id			 = sanitize_text_field( $_POST['rapaygo_pp_live_client_id']);
	$test_client_id			 = sanitize_text_field( $_POST['rapaygo_pp_test_client_id']);
	$live_secret			 = sanitize_text_field( $_POST['rapaygo_pp_live_secret']);
	$test_secret			 = sanitize_text_field( $_POST['rapaygo_pp_test_secret']);
	$disable_standard_checkout	 = filter_input( INPUT_POST, 'rapaygo_disable_standard_checkout', FILTER_SANITIZE_NUMBER_INT );
	$btn_size			 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_btn_size']);
	$btn_color			 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_btn_color']);
	$btn_shape			 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_btn_shape']);
	$btn_layout			 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_btn_layout']);
	$pm_credit			 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_payment_method_credit']);
	$pm_elv				 = sanitize_text_field( $_POST['rapaygo_pp_smart_checkout_payment_method_elv']);

	update_option( 'rapaygo_enable_pp_smart_checkout', $enable_pp_smart_checkout );
	update_option( 'rapaygo_pp_live_client_id', $live_client_id );
	update_option( 'rapaygo_pp_live_secret', $live_secret );
	update_option( 'rapaygo_pp_test_client_id', $test_client_id );
	update_option( 'rapaygo_pp_test_secret', $test_secret );
	update_option( 'rapaygo_disable_standard_checkout', $disable_standard_checkout );
	update_option( 'rapaygo_pp_smart_checkout_btn_size', $btn_size );
	update_option( 'rapaygo_pp_smart_checkout_btn_color', $btn_color );
	update_option( 'rapaygo_pp_smart_checkout_btn_shape', $btn_shape );
	update_option( 'rapaygo_pp_smart_checkout_btn_layout', $btn_layout );
	update_option( 'rapaygo_pp_smart_checkout_payment_method_credit', $pm_credit );
	update_option( 'rapaygo_pp_smart_checkout_payment_method_elv', $pm_elv );

	echo '<div id="message" class="updated fade"><p><strong>';
	echo 'Advanced Settings Updated!';
	echo '</strong></p></div>';
    }
    $rapaygo_send_buyer_email = '';
    if ( get_option( 'rapaygo_send_buyer_email' ) ) {
	$rapaygo_send_buyer_email = 'checked="checked"';
    }
    $rapaygo_buyer_from_email	 = get_option( 'rapaygo_buyer_from_email' );
    $rapaygo_buyer_email_subj	 = get_option( 'rapaygo_buyer_email_subj' );
    $rapaygo_buyer_email_body	 = get_option( 'rapaygo_buyer_email_body' );
    $rapaygo_send_seller_email = '';
    if ( get_option( 'rapaygo_send_seller_email' ) ) {
	$rapaygo_send_seller_email = 'checked="checked"';
    }
    $rapaygo_notify_email_address = get_option( 'rapaygo_notify_email_address' );
    if ( empty( $rapaygo_notify_email_address ) ) {
	$rapaygo_notify_email_address = get_bloginfo( 'admin_email' ); //default value
    }
    $rapaygo_seller_email_subj = get_option( 'rapaygo_seller_email_subj' );
    if ( empty( $rapaygo_seller_email_subj ) ) {
	$rapaygo_seller_email_subj = "Notification of product sale";
    }
    $rapaygo_seller_email_body = get_option( 'rapaygo_seller_email_body' );
    if ( empty( $rapaygo_seller_email_body ) ) {
	$rapaygo_seller_email_body = "Dear Seller\n" .
	"\nThis mail is to notify you of a product sale.\n" .
	"\n{product_details}" .
	"\n\nThe sale was made to {first_name} {last_name} ({payer_email})" .
	"\n\nThanks";
    }
    ?>

    <div class="rapaygo_yellow_box">
        <p><?php _e( "For more information, updates, detailed documentation and video tutorial, please visit:", "rapaygo-wp-plugin" ); ?><br />
    	<a href="https://www.tipsandtricks-hq.com/rapaygo-wp-plugin-plugin-768" target="_blank"><?php _e( "WP Simple Cart Homepage", "rapaygo-wp-plugin" ); ?></a></p>
    </div>

    <form method="post" action="">
	<?php wp_nonce_field( 'rapaygo_adv_settings_update' ); ?>
        <input type="hidden" name="info_update" id="info_update" value="true" />

        <div class="postbox">
    	<h3 class="hndle">
    	    <label for="title"><?php _e( "PayPal Smart Checkout Settings", "rapaygo-wp-plugin" ); ?></label>
    	</h3>
    	<div class="inside">

    	    <table class="form-table">

    		<tr valign="top">
    		    <th scope="row"><?php _e( "Enable PayPal Smart Checkout", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="checkbox" name="rapaygo_enable_pp_smart_checkout" value="1"<?php echo get_option( 'rapaygo_enable_pp_smart_checkout' ) ? ' checked' : ''; ?>/>
    			<span class="description">
                            <?php _e( "Enable PayPal Smart Checkout.", "rapaygo-wp-plugin" ); ?>
                            <?php echo '<a href="https://www.tipsandtricks-hq.com/ecommerce/enabling-smart-button-checkout-setup-and-configuration-4568" target="_blank">' . __( "View Documentation", "rapaygo-wp-plugin" ) . '</a>.'; ?>
                        </span>

    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Live Client ID", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="text" name="rapaygo_pp_live_client_id" size="100" value="<?php echo esc_attr( get_option( 'rapaygo_pp_live_client_id' ) ); ?>"/>
    			<span class="description"><?php _e( "Enter your live Client ID.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Live Secret", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="text" name="rapaygo_pp_live_secret" size="100" value="<?php echo esc_attr( get_option( 'rapaygo_pp_live_secret' ) ); ?>"/>
    			<span class="description"><?php _e( "Enter your live Secret.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Sandbox Client ID", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="text" name="rapaygo_pp_test_client_id" size="100" value="<?php echo esc_attr( get_option( 'rapaygo_pp_test_client_id' ) ); ?>"/>
    			<span class="description"><?php _e( "Enter your sandbox Client ID.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Sandbox Secret", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="text" name="rapaygo_pp_test_secret" size="100" value="<?php echo esc_attr( get_option( 'rapaygo_pp_test_secret' )); ?>"/>
    			<span class="description"><?php _e( "Enter your sandbox Secret.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>

    	    </table>

    	    <h4><?php _e( "Button Appearance Settings", "rapaygo-wp-plugin" ); ?></h4>
    	    <hr />

    	    <table class="form-table">
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Size", "rapaygo-wp-plugin" ); ?></th>
    		    <td>
    			<select name="rapaygo_pp_smart_checkout_btn_size">
				<?php
				$btn_size	 = get_option( 'rapaygo_pp_smart_checkout_btn_size' );
				echo WPSPCAdminUtils::gen_options( array(
				    array( 'medium', __( "Medium", "rapaygo-wp-plugin" ) ),
				    array( 'large', __( "Large", "rapaygo-wp-plugin" ) ),
				    array( 'responsive', __( "Repsonsive", "rapaygo-wp-plugin" ) ),
				), $btn_size );
				?>
    			</select>
    			<span class="description"><?php _e( "Select button size.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Color", "rapaygo-wp-plugin" ); ?></th>
    		    <td>
    			<select name="rapaygo_pp_smart_checkout_btn_color">
				<?php
				$btn_color	 = get_option( 'rapaygo_pp_smart_checkout_btn_color' );
				echo WPSPCAdminUtils::gen_options( array(
				    array( 'gold', __( "Gold", "rapaygo-wp-plugin" ) ),
				    array( 'blue', __( "Blue", "rapaygo-wp-plugin" ) ),
				    array( 'silver', __( "Silver", "rapaygo-wp-plugin" ) ),
				    array( 'black', __( "Black", "rapaygo-wp-plugin" ) ),
				), $btn_color );
				?>
    			</select>
    			<span class="description"><?php _e( "Select button color.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
		    <?php
		    $btn_layout	 = get_option( 'rapaygo_pp_smart_checkout_btn_layout' );
		    $btn_shape	 = get_option( 'rapaygo_pp_smart_checkout_btn_shape' );
		    ?>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Shape", "rapaygo-wp-plugin" ); ?></th>
    		    <td>
    			<p><label><input type="radio" name="rapaygo_pp_smart_checkout_btn_shape" value="rect"<?php WPSPCAdminUtils::e_checked( $btn_shape, 'rect', true ); ?>> <?php _e( "Rectangular ", "rapaygo-wp-plugin" ); ?></label></p>
    			<p><label><input type="radio" name="rapaygo_pp_smart_checkout_btn_shape" value="pill"<?php WPSPCAdminUtils::e_checked( $btn_shape, 'pill' ); ?>> <?php _e( "Pill", "rapaygo-wp-plugin" ); ?></label></p>
    			<p class="description"><?php _e( "Select button shape.", "rapaygo-wp-plugin" ); ?></p>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Layout", "rapaygo-wp-plugin" ); ?></th>
    		    <td>
    			<p><label><input type="radio" name="rapaygo_pp_smart_checkout_btn_layout" value="vertical"<?php WPSPCAdminUtils::e_checked( $btn_layout, 'vertical', true ); ?>> <?php _e( "Vertical", "rapaygo-wp-plugin" ); ?></label></p>
    			<p><label><input type="radio" name="rapaygo_pp_smart_checkout_btn_layout" value="horizontal"<?php WPSPCAdminUtils::e_checked( $btn_layout, 'horizontal' ); ?>> <?php _e( "Horizontal", "rapaygo-wp-plugin" ); ?></label></p>
    			<p class="description"><?php _e( "Select button layout.", "rapaygo-wp-plugin" ); ?></p>
    		    </td>
    		</tr>
    	    </table>

    	    <h4><?php _e( "Additional Settings", "rapaygo-wp-plugin" ); ?></h4>
    	    <hr />
		<?php
		$pm_credit	 = get_option( 'rapaygo_pp_smart_checkout_payment_method_credit' );
		$pm_elv		 = get_option( 'rapaygo_pp_smart_checkout_payment_method_elv' );
		?>
    	    <table class="form-table">
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Payment Methods", "rapaygo-wp-plugin" ); ?></th>
    		    <td>
    			<p><label><input type="checkbox" name="rapaygo_pp_smart_checkout_payment_method_credit" value="1"<?php WPSPCAdminUtils::e_checked( $pm_credit ); ?>> <?php _e( "PayPal Credit", "rapaygo-wp-plugin" ); ?></label></p>
    			<p><label><input type="checkbox" name="rapaygo_pp_smart_checkout_payment_method_elv" value="1"<?php WPSPCAdminUtils::e_checked( $pm_elv ); ?>> <?php _e( "ELV", "rapaygo-wp-plugin" ); ?></label></p>
    			<p class="description"><?php _e( "Select payment methods that could be used by customers. Note that payment with cards is always enabled.", "rapaygo-wp-plugin" ); ?></p>
    		    </td>
    		</tr>
    		<tr valign="top">
    		    <th scope="row"><?php _e( "Disable Standard PayPal Checkout", "rapaygo-wp-plugin" ); ?></th>
    		    <td><input type="checkbox" name="rapaygo_disable_standard_checkout" value="1"<?php echo get_option( 'rapaygo_disable_standard_checkout' ) ? ' checked' : ''; ?>/>
    			<span class="description"><?php _e( "By default PayPal standard checkout is always enabled. If you only want to use the PayPal Smart Checkout instead then use this checkbox to disable the standard checkout option. This option will only have effect when Smart Checkout is enabled.", "rapaygo-wp-plugin" ); ?></span>
    		    </td>
    		</tr>
    	    </table>

    	</div>
        </div>

        <div class="submit">
    	<input type="submit" class="button-primary" name="rapaygo_adv_settings_update" value="<?php echo (__( "Update Options &raquo;", "rapaygo-wp-plugin" )) ?>" />
        </div>
    </form>

    <?php
    rapaygo_settings_menu_footer();
}
