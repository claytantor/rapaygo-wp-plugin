<?php

function rapaygo_show_addons_menu_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }
    $output = "";

    echo '<div class="wrap">';
    echo '<h1>' . (__("Simple Cart Add-ons", "rapaygo-wp-plugin")) . '</h1>';

    echo '<div id="poststuff"><div id="post-body">';
    ?>

    <div class="rapaygo_yellow_box">
        <p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "rapaygo-wp-plugin"); ?><br />
            <a href="https://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart" target="_blank"><?php _e("WP Simple Cart Documentation", "rapaygo-wp-plugin"); ?></a></p>
    </div>

    <?php
    $addons_data = array();

    $addon_1 = array(
        "name"		 => __( "Collect Customer Input", 'rapaygo-wp-plugin' ),
        "thumbnail"	 => RAPAYGO_CART_URL . "/includes/admin/images/rapaygo-customer-input.png",
        "description"	 => __( "This addon allows you to collect customer input in the shopping cart at the time of checkout.", 'rapaygo-wp-plugin' ),
        "page_url"	 => "https://www.tipsandtricks-hq.com/ecommerce/wp-simple-cart-collect-customer-input-in-the-shopping-cart-4396",
    );
    array_push( $addons_data, $addon_1 );

    $addon_2 = array(
        "name"		 => __( "Mailchimp Integration", 'rapaygo-wp-plugin' ),
        "thumbnail"	 => RAPAYGO_CART_URL . "/includes/admin/images/rapaygo-mailchimp-integration.png",
        "description"	 => __( "This addon allows you to add users to your Mailchimp list after they purchase an item.", 'rapaygo-wp-plugin' ),
        "page_url"	 => "https://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart-and-mailchimp-integration-3442",
    );
    array_push( $addons_data, $addon_2 );

    $addon_3 = array(
        "name"		 => __( "WP Affiliate Plugin", 'rapaygo-wp-plugin' ),
        "thumbnail"	 => RAPAYGO_CART_URL . "/includes/admin/images/wp-affiliate-plugin-integration.png",
        "description"	 => __( "This plugin allows you to award commission to affiliates for referring customers to your site.", 'rapaygo-wp-plugin' ),
        "page_url"	 => "https://www.tipsandtricks-hq.com/wordpress-affiliate-platform-plugin-simple-affiliate-program-for-wordpress-blogsite-1474",
    );
    array_push( $addons_data, $addon_3 );

    /* Show the addons list */
    foreach ( $addons_data as $addon ) {
        $output .= '<div class="rapaygo_addon_item_canvas">';

        $output .= '<div class="rapaygo_addon_item_thumb">';

        $img_src = $addon[ 'thumbnail' ];
        $output	 .= '<img src="' . $img_src . '" alt="' . $addon[ 'name' ] . '">';
        $output	 .= '</div>'; //end thumbnail

        $output	 .= '<div class="rapaygo_addon_item_body">';
        $output	 .= '<div class="rapaygo_addon_item_name">';
        $output	 .= '<a href="' . $addon[ 'page_url' ] . '" target="_blank">' . $addon[ 'name' ] . '</a>';
        $output	 .= '</div>'; //end name

        $output	 .= '<div class="rapaygo_addon_item_description">';
        $output	 .= $addon[ 'description' ];
        $output	 .= '</div>'; //end description

        $output	 .= '<div class="rapaygo_addon_item_details_link">';
        $output	 .= '<a href="' . $addon[ 'page_url' ] . '" class="rapaygo_addon_view_details" target="_blank">' . __( 'View Details', 'rapaygo-wp-plugin' ) . '</a>';

        $output	 .= '</div>'; //end detils link
        $output	 .= '</div>'; //end body

        $output .= '</div>'; //end canvas
    }

    echo $output;

    echo '</div></div>';//End of poststuff and post-body
    echo '</div>';//End of wrap

}