<?php

function rapaygo_show_tools_menu_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';
    echo '<h1>' . (__("Simple Cart Tools", "rapaygo-wp-plugin")) . '</h1>';
    
    echo '<div id="poststuff"><div id="post-body">';
    
    if (isset($_POST['rapaygo_export_orders_data'])) {
        $nonce = $_REQUEST['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'rapaygo_tools_export_orders_data')) {
            wp_die('Error! Nonce Security Check Failed! Go back to Tools menu and try again.');
        }

        $file_url = rapaygo_export_orders_data_to_csv();
        $export_message = 'Data exported to <a href="' . $file_url . '" target="_blank">Orders Data File (Right click on this link and choose "Save As" to save the file to your computer)</a>';
        echo '<div id="message" class="updated fade"><p><strong>';
        echo $export_message;
        echo '</strong></p></div>';
    }
    ?>

    <div class="rapaygo_yellow_box">
        <p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "rapaygo-wp-plugin"); ?><br />
            <a href="https://www.tipsandtricks-hq.com/rapaygo-wp-plugin-plugin-768" target="_blank"><?php _e("WP Simple Cart Homepage", "rapaygo-wp-plugin"); ?></a></p>
    </div>

    <div class="postbox">
        <h3 class="hndle"><label for="title"><?php _e("Export Cart Orders Data", "rapaygo-wp-plugin"); ?></label></h3>
        <div class="inside">
            <form method="post" action="">
                <?php wp_nonce_field('rapaygo_tools_export_orders_data'); ?>

                <p><?php _e("You can use this option to export all the orders data to a CSV/Excel file.", "rapaygo-wp-plugin"); ?></p>
                <div class="submit">
                    <input type="submit" name="rapaygo_export_orders_data" class="button-primary" value="<?php echo (__("Export Data", "rapaygo-wp-plugin")) ?>" />
                </div>

            </form>
        </div>
    </div>
    <?php
    
    rapaygo_settings_menu_footer();
    
    echo '</div></div>';//End of poststuff and post-body
    echo '</div>';//End of wrap
    
}

function rapaygo_export_orders_data_to_csv(){
    
    $file_path = RAPAYGO_CART_PATH . "includes/admin/exported_orders_data.csv";
    $fp = fopen($file_path, 'w');
    
    $header_names = array("Order ID", "Transaction ID", "Date", "First Name", "Last Name", "Email", "IP Address", "Total", "Shipping", "Coupon Code", "Address", "Items Orders");
    
    $header_names=apply_filters('rapaygo_export_csv_header',$header_names);
    
    fputcsv($fp, $header_names);
    
    $query_args = array(
        'post_type' => 'wpsc_cart_orders',
        'numberposts' => -1, /* to retrieve all posts */
        'orderby' => 'date',
	'order' => 'DESC',
    );
    $posts_array = get_posts( $query_args );
    
    foreach ($posts_array as $item) {
        $order_id = $item->ID;
        $txn_id = get_post_meta( $order_id, 'wpsc_txn_id', true );
        $order_date = $item->post_date;
        $first_name = get_post_meta( $order_id, 'wpsc_first_name', true );
        $last_name = get_post_meta( $order_id, 'wpsc_last_name', true );
        $email = get_post_meta( $order_id, 'wpsc_email_address', true );
        $ip_address = get_post_meta( $order_id, 'wpsc_ipaddress', true );
        $total_amount = get_post_meta( $order_id, 'wpsc_total_amount', true );
        $shipping_amount = get_post_meta( $order_id, 'wpsc_shipping_amount', true );
        $address = get_post_meta( $order_id, 'wpsc_address', true );
        $phone = get_post_meta( $order_id, 'rapaygo_phone', true );      
        $applied_coupon = get_post_meta( $order_id, 'wpsc_applied_coupon', true );

        $items_ordered = get_post_meta( $order_id, 'rapaygo_items_ordered', true );
        $items_ordered = str_replace(array("\n", "\r", "\r\n", "\n\r"), ' ', $items_ordered);

        $fields = array($order_id, $txn_id, $order_date, $first_name, $last_name, $email, $ip_address, $total_amount, $shipping_amount, $applied_coupon, $address, $items_ordered);
	
	$fields=apply_filters('rapaygo_export_csv_data',$fields,$order_id);
	
        fputcsv($fp, $fields);
        
    }
    
    fclose($fp);

    $file_url = RAPAYGO_CART_URL . '/includes/admin/exported_orders_data.csv';
    return $file_url;
    
}