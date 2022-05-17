<?php
global $carts_cnt;
$carts_cnt = 0;

function print_rapaygo_shopping_cart( $args = array() ) {
	$output = '';
	global $carts_cnt;
	$carts_cnt ++;
	if ( ! cart_not_empty() ) {
		$empty_cart_text = get_option( 'rapaygo_cart_empty_text' );
		if ( ! empty( $empty_cart_text ) ) {
			$output .= '<div class="rapaygo_cart_empty_cart_section">';
			if ( preg_match( '/http/', $empty_cart_text ) ) {
				$output .= '<img src="' . $empty_cart_text . '" alt="' . $empty_cart_text . '" class="rapaygo_cart_empty_cart_image" />';
			} else {
				$output .= __( $empty_cart_text, 'rapaygo-wp-plugin' );
			}
			$output .= '</div>';
		}
		$cart_products_page_url = get_option( 'cart_products_page_url' );
		if ( ! empty( $cart_products_page_url ) ) {
			$output .= '<div class="rapaygo_cart_visit_shop_link"><a rel="nofollow" href="' . esc_url( $cart_products_page_url ) . '">' . ( __( 'Visit The Shop', 'rapaygo-wp-plugin' ) ) . '</a></div>';
		}
		return $output;
	}
	$email                  = get_bloginfo( 'admin_email' );
	$use_affiliate_platform = get_option( 'wp_use_aff_platform' );
	$defaultCurrency        = get_option( 'cart_payment_currency' );
	$defaultSymbol          = get_option( 'cart_currency_symbol' );
	$defaultEmail           = get_option( 'cart_paypal_email' );
	if ( ! empty( $defaultCurrency ) ) {
		$rapaygo_currency = $defaultCurrency;
	} else {
		$rapaygo_currency = __( 'USD', 'rapaygo-wp-plugin' );
	}
	if ( ! empty( $defaultSymbol ) ) {
		$rapaygo_symbol = $defaultSymbol;
	} else {
		$rapaygo_symbol = __( '$', 'rapaygo-wp-plugin' );
	}

	if ( ! empty( $defaultEmail ) ) {
		$email = $defaultEmail;
	}

	$decimal = '.';
	$urls    = '';

	$return = get_option( 'cart_return_from_paypal_url' );
	if ( empty( $return ) ) {
		$return = RAPAYGO_CART_SITE_URL . '/';
	}
	$return_url = add_query_arg( 'reset_rapaygo_cart', '1', $return );

	$urls .= '<input type="hidden" name="return" value="' . $return_url . '" />';

	$cancel = get_option( 'cart_cancel_from_paypal_url' );
	if ( isset( $cancel ) && ! empty( $cancel ) ) {
		$urls .= '<input type="hidden" name="cancel_return" value="' . $cancel . '" />';
	}

	$notify = RAPAYGO_CART_SITE_URL . '/?simple_cart_ipn=1';
	$notify = apply_filters( 'rapaygo_paypal_ipn_notify_url', $notify );
	$urls  .= '<input type="hidden" name="notify_url" value="' . $notify . '" />';

	$title = get_option( 'rapaygo_cart_title' );
	//if (empty($title)) $title = __("Your Shopping Cart", "rapaygo-wp-plugin");

	global $plugin_dir_name;
	$output .= '<div class="shopping_cart">';
	if ( ! get_option( 'rapaygo_image_hide' ) ) {
		$cart_icon_img_src = RAPAYGO_CART_URL . '/images/shopping_cart_icon.png';
		$cart_icon_img_src = apply_filters( 'rapaygo_cart_icon_image_src', $cart_icon_img_src );
		$output           .= "<div class='rapaygo_cart_header_image_container' ><img src='" . $cart_icon_img_src . "' class='rapaygo_cart_header_image' value='" . ( __( 'Cart', 'rapaygo-wp-plugin' ) ) . "' alt='" . ( __( 'Cart', 'rapaygo-wp-plugin' ) ) . "' /></div>";
	}
	if ( ! empty( $title ) ) {
		$output .= '<h4 class="rapaygo-cart-title">';
		$output .= $title;
		$output .= '</h4>';
	}

	$output .= '<span id="pinfo" style="display: none; font-weight: bold; color: red;">' . ( __( 'Hit enter to submit new Quantity.', 'rapaygo-wp-plugin' ) ) . '</span>';
	$output .= '<table style="width: 100%;">';

	$count       = 1;
	$total_items = 0;
	$total       = 0;
	$form        = '';
	if ( $_SESSION['simpleCart'] && is_array( $_SESSION['simpleCart'] ) ) {
		$output             .= '
        <tr class="rapaygo_cart_item_row">
        <th class="rapaygo_cart_item_name_th">' . ( __( 'Item Name', 'rapaygo-wp-plugin' ) ) . '</th><th class="rapaygo_cart_qty_th">' . ( __( 'QTY', 'rapaygo-wp-plugin' ) ) . '</th><th class="rapaygo_cart_price_th">' . ( __( 'Price', 'rapaygo-wp-plugin' ) ) . '</th><th class="rapaygo_remove_item_th"></th>
        </tr>';
		$item_total_shipping = 0;
		$postage_cost        = 0;
		foreach ( $_SESSION['simpleCart'] as $item ) {
			$total               += $item['price'] * $item['quantity'];
			$item_total_shipping += $item['shipping'] * $item['quantity'];
			$total_items         += $item['quantity'];
		}
		if ( ! empty( $item_total_shipping ) ) {
			$baseShipping = get_option( 'cart_base_shipping_cost' );
			$postage_cost = $item_total_shipping + $baseShipping;
		}

		$cart_free_shipping_threshold = get_option( 'cart_free_shipping_threshold' );
		if ( ! empty( $cart_free_shipping_threshold ) && $total > $cart_free_shipping_threshold ) {
			$postage_cost = 0;
		}

		$item_tpl   = "{name: '%s', quantity: '%d', price: '%s', currency: '" . $rapaygo_currency . "'}";
		$items_list = '';

		foreach ( $_SESSION['simpleCart'] as $item ) {
			//Let's form JS array of items for Smart Checkout
                        $number_formatted_item_price = rapaygo_number_format_price($item['price']);
			$items_list .= sprintf( $item_tpl, esc_js( $item['name'] ), esc_js( $item['quantity'] ), esc_js( $number_formatted_item_price ) ) . ',';

			$output .= '<tr class="rapaygo_cart_item_thumb"><td class="rapaygo_cart_item_name_td" style="overflow: hidden;">';
			$output .= '<div class="rapaygo_cart_item_info">';
			if ( isset( $args['show_thumbnail'] ) ) {
				$output .= '<span class="rapaygo_cart_item_thumbnail"><img src="' . esc_url( $item['thumbnail'] ) . '" class="rapaygo_cart_thumb_image" alt="' . esc_attr( $item['name'] ) . '" ></span>';
			}
			$item_info = apply_filters( 'rapaygo_cart_item_name', '<a href="' . esc_url( $item['cartLink'] ) . '">' . esc_attr( $item['name'] ) . '</a>', $item );
			$output   .= '<span class="rapaygo_cart_item_name">' . $item_info . '</span>';
			$output   .= '<span class="rapaygo_cart_clear_float"></span>';
			$output   .= '</div>';
			$output   .= '</td>';

			$uniqid = uniqid();

			$output .= "<td class='rapaygo_cart_qty_td' style='text-align: center'><form method=\"post\"  action=\"\" name='pcquantity_" . $uniqid . "' style='display: inline'>" . wp_nonce_field( 'rapaygo_cquantity', '_wpnonce', true, false ) . '
                <input type="hidden" name="rapaygo_product" value="' . htmlspecialchars( $item['name'] ) . "\" />
	        <input type='hidden' name='cquantity' value='1' /><input style='width: 40px;' type='number' class='rapaygo_cart_item_qty' name='quantity' value='" . esc_attr( $item['quantity'] ) . "' min='0' step='1' size='3' onchange='document.pcquantity_" . $uniqid . ".submit();' onkeypress='document.getElementById(\"pinfo\").style.display = \"\";' /></form></td>
	        <td class='rapaygo_cart_price_td'>" . print_payment_currency( ( $item['price'] * $item['quantity'] ), $rapaygo_symbol, $decimal ) . "</td>
	        <td class='rapaygo_remove_item_td'><form method=\"post\" action=\"\" class=\"rapaygo_cart_remove_item_form\">" . wp_nonce_field( 'rapaygo_delcart', '_wpnonce', true, false ) . '
	        <input type="hidden" name="rapaygo_product" value="' . esc_attr( $item['name'] ) . "\" />
	        <input type='hidden' name='delcart' value='1' />
	        <input type='image' src='" . RAPAYGO_CART_URL . "/images/Shoppingcart_delete.png' value='" . ( __( 'Remove', 'rapaygo-wp-plugin' ) ) . "' title='" . ( __( 'Remove', 'rapaygo-wp-plugin' ) ) . "' /></form></td></tr>
	        ";

			$form .= "
	            <input type=\"hidden\" name=\"item_name_$count\" value=\"" . esc_attr( $item['name'] ) . "\" />
	            <input type=\"hidden\" name=\"amount_$count\" value='" . rapaygo_number_format_price( $item['price'] ) . "' />
	            <input type=\"hidden\" name=\"quantity_$count\" value=\"" . esc_attr( $item['quantity'] ) . "\" />
	            <input type='hidden' name='item_number_$count' value='" . esc_attr( $item['item_number'] ) . "' />
	        ";
			$count ++;
		}
		$items_list = rtrim( $items_list, ',' );
		if ( ! get_option( 'rapaygo_use_profile_shipping' ) ) {
			//Not using profile based shipping
			$postage_cost = rapaygo_number_format_price( $postage_cost );
			$form        .= "<input type=\"hidden\" name=\"shipping_1\" value='" . esc_attr( $postage_cost ) . "' />"; //You can also use "handling_cart" variable to use shipping and handling here
		}

		//Tackle the "no_shipping" parameter
		if ( get_option( 'rapaygo_collect_address' ) ) {//force address collection
			$form .= '<input type="hidden" name="no_shipping" value="2" />';
		} else {
			//Not using the force address collection feature
			if ( $postage_cost == 0 ) {
				//No shipping amount present in the cart. Set flag for "no shipping address collection".
				$form .= '<input type="hidden" name="no_shipping" value="1" />';
			}
		}
	}

	$count --;

	if ( $count ) {
		if ( $postage_cost != 0 ) {
			$output .= "
                <tr class='rapaygo_cart_subtotal'><td colspan='2' style='font-weight: bold; text-align: right;'>" . ( __( 'Subtotal', 'rapaygo-wp-plugin' ) ) . ": </td><td style='text-align: center'>" . print_payment_currency( $total, $rapaygo_symbol, $decimal ) . "</td><td></td></tr>
                <tr class='rapaygo_cart_shipping'><td colspan='2' style='font-weight: bold; text-align: right;'>" . ( __( 'Shipping', 'rapaygo-wp-plugin' ) ) . ": </td><td style='text-align: center'>" . print_payment_currency( $postage_cost, $rapaygo_symbol, $decimal ) . '</td><td></td></tr>';
		}

		$output .= "<tr class='rapaygo_cart_total'><td colspan='2' style='font-weight: bold; text-align: right;'>" . ( __( 'Total', 'rapaygo-wp-plugin' ) ) . ": </td><td class='rapaygo_cart_total_td'>" . print_payment_currency( ( $total + $postage_cost ), $rapaygo_symbol, $decimal ) . '</td><td></td></tr>';

		if ( isset( $_SESSION['rapaygo_cart_action_msg'] ) && ! empty( $_SESSION['rapaygo_cart_action_msg'] ) ) {
			$output .= '<tr class="rapaygo_cart_action_msg"><td colspan="4"><span class="rapaygo_cart_action_msg">' . $_SESSION['rapaygo_cart_action_msg'] . '</span></td></tr>';
		}

		if ( get_option( 'rapaygo_enable_coupon' ) == '1' ) {
			$output .= '<tr class="rapaygo_cart_coupon_row"><td colspan="4">
                <div class="rapaygo_coupon_section">
                <span class="rapaygo_coupon_label">' . ( __( 'Enter Coupon Code', 'rapaygo-wp-plugin' ) ) . '</span>
                <form  method="post" action="" >' . wp_nonce_field( 'rapaygo_coupon', '_wpnonce', true, false ) . '
                <input type="text" name="rapaygo_coupon_code" value="" size="10" />
                <span class="rapaygo_coupon_apply_button"><input type="submit" name="rapaygo_apply_coupon" class="rapaygo_apply_coupon" value="' . ( __( 'Apply', 'rapaygo-wp-plugin' ) ) . '" /></span>
                </form>
                </div>
                </td></tr>';
		}

		$rapaygo_checkout_url = RAPAYGO_CART_SUBMIT_URL;
		if ( get_option( 'rapaygo_enable_sandbox' ) ) {
			$rapaygo_checkout_url = RAPAYGO_CART_DEV_SUBMIT_URL;
		}

		// $form_target_code = '';
		// if ( get_option( 'rapaygo_open_pp_checkout_in_new_tab' ) ) {
		// 	$form_target_code = 'target="_blank"';
		// }

		// $output = apply_filters( 'rapaygo_before_checkout_form', $output );

		$output .= "<tr class='rapaygo_checkout_form'><td colspan='4'>";
		$output .= '<form action="' . $rapaygo_checkout_url . '" method="post" ' . $form_target_code . '>';
		$output .= $form;
		// $style   = get_option( 'rapaygo_disable_standard_checkout' ) && get_option( 'rapaygo_enable_pp_smart_checkout' ) ? 'display:none !important" data-rapaygo-hidden="1' : '';

		// we need to hide the pay button until other information is filled in
		$style   = 'display:none !important" data-rapaygo-hidden="1';
		if ( $count ) {

			$btn_checkout = '<a class="rapaygo_button rapaygo_cart_checkout_button_' . $carts_cnt . '">Checkout Now</a>';
			$output      .= '<div class="rapaygo_cart_checkout_button_container">' . $btn_checkout . '</div>';

			// the add to cart button is hidden until other information is filled in			
			$address_fields_fname = '<input class="rapaygo_cart_addr_form" type="text" prompt="First name" name="first_name" value="' . esc_attr( $fname ) . '" size="30" />';

			$address_fields_lname = '<input class="rapaygo_cart_addr_form" type="text" prompt="Last name" name="last_name" value="' . esc_attr( $lname ) . '" size="30" />';

			$address_fields_email = '<input class="rapaygo_cart_addr_form" type="text" prompt="Email" name="email" value="' . esc_attr( $email ) . '" size="30" />';

			$address_fields_phone = '<input class="rapaygo_cart_addr_form" type="text" prompt="Phone" name="phone" value="' . esc_attr( $phone ) . '" size="30" />';

			$address_fields_address = '<input class="rapaygo_cart_addr_form" type="text" prompt="Address" name="address" value="' . esc_attr( $address ) . '" size="30" />';

			$address_fields_city = '<input class="rapaygo_cart_addr_form" type="text" prompt="City" name="city" value="' . esc_attr( $city ) . '" size="30" />';

			$address_fields_state = '<input class="rapaygo_cart_addr_form" type="text" prompt="State" name="state" value="' . esc_attr( $state ) . '" size="30" />';

			$address_fields_zip = '<input class="rapaygo_cart_addr_form" type="text" prompt="Zip" name="zip" value="' . esc_attr( $zip ) . '" size="30" />';

			$address_fields_country = '<input class="rapaygo_cart_addr_form" type="text" prompt="Country" name="country" value="' . esc_attr( $country ) . '" size="30" />';

			$address_style = 'display:none !important" data-rapaygo-hidden="1';

			$output      .= '<div style="' . $address_style . '" class="rapaygo_cart_address_container">'. $address_fields_fname . $address_fields_lname . 
				$address_fields_email . $address_fields_phone . $address_fields_address . $address_fields_city . $address_fields_state . $address_fields_zip . $address_fields_country . '</div>';

			


			// $checkout_button_img_src = RAPAYGO_CART_URL . '/images/' . ( __( 'rapaygo_checkout_EN.png', 'rapaygo-wp-plugin' ) );
			// $output                 .= '<input type="image" src="' . apply_filters( 'rapaygo_cart_checkout_button_image_src', $checkout_button_img_src ) . '" name="submit" class="rapaygo_cart_checkout_button rapaygo_cart_checkout_button_' . $carts_cnt . '" style="' . $style . '" alt="' . ( __( "Make payments with Rapaygo - Everything you need to accept Bitcoin payments", 'rapaygo-wp-plugin' ) ) . '" />';
		}

		$output .= $urls . '
            <input type="hidden" name="business" value="' . $email . '" />
            <input type="hidden" name="currency_code" value="' . $rapaygo_currency . '" />
            <input type="hidden" name="cmd" value="_cart" />
            <input type="hidden" name="upload" value="1" />
            <input type="hidden" name="rm" value="2" />
            <input type="hidden" name="charset" value="utf-8" />
            <input type="hidden" name="bn" value="TipsandTricks_SP" />';

		// $page_style_name = get_option( 'rapaygo_cart_paypal_co_page_style' );
		// if ( ! empty( $page_style_name ) ) {
		// 	$output .= '<input type="hidden" name="image_url" value="' . $page_style_name . '" />';
		// }

		$output .= rapaygo_cart_add_custom_field();

		// $extra_pp_fields = apply_filters( 'rapaygo_cart_extra_paypal_fields', '' ); //Can be used to add extra PayPal hidden input fields for the cart checkout
		// $output         .= $extra_pp_fields;

		$output .= '</form>';
		$output .= '</td></tr>';
		?>
		<script>
			jQuery(document).ready(function ($) {				
				$('.rapaygo_cart_checkout_button_<?php echo $carts_cnt; ?>').click(function () {
					$('.rapaygo_cart_checkout_button_<?php echo $carts_cnt; ?>').attr('disabled', 'disabled');
					$('.rapaygo_cart_checkout_button_<?php echo $carts_cnt; ?>').attr('data-rapaygo-hidden', '1');
					$('.rapaygo_cart_checkout_button_<?php echo $carts_cnt; ?>').attr('style', 'display:none !important');
				});
			});
		</script>
		<style>
			.rapaygo_cart_checkout_button_<?php echo $carts_cnt; ?> {
				
			}
			a.rapaygo_button {
				background-color: #fcba03;
				color: #fff;
				padding: 30px;
				border-radius: 5px;
				font-size: 1.0em;
				font-weight: bold;
				display: block;
				margin: 10px auto;
				text-align: center;
			}
			.rapaygo_button:hover {
				background-color: #8c6803;
				color: #fff;
			}
		</style>
		<?php
	}
	$output .= '</table></div>';
	$output  = apply_filters( 'rapaygo_after_cart_output', $output );
	return $output;
}
