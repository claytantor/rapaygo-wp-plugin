<?php

//Handle the admin dashboard main menu
add_action('admin_menu', 'rapaygo_handle_admin_menu');

// Handle the options page display
function rapaygo_handle_admin_menu() {

    include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_discounts.php');
    include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_tools.php');
    include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_addons.php');
    
    $menu_icon_url = 'dashicons-cart';
    add_menu_page(__('rapaygo Cart', 'rapaygo-wp-plugin'), __('rapaygo Cart', 'rapaygo-wp-plugin'), RAPAYGO_CART_MANAGEMENT_PERMISSION, RAPAYGO_CART_MAIN_MENU_SLUG , 'rapaygo_settings_interface', $menu_icon_url, 90);
    add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Settings', 'rapaygo-wp-plugin'),  __('Settings', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, RAPAYGO_CART_MAIN_MENU_SLUG, 'rapaygo_settings_interface');
    add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Coupons', 'rapaygo-wp-plugin'),  __('Coupons', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, 'rapaygo-discounts', 'rapaygo_show_coupon_discount_settings_page');
    add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Tools', 'rapaygo-wp-plugin'),  __('Tools', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, 'rapaygo-tools', 'rapaygo_show_tools_menu_page');
    add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Add-ons', 'rapaygo-wp-plugin'),  __('Add-ons', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, 'rapaygo-addons', 'rapaygo_show_addons_menu_page');
        
    //Can set the "show_in_menu" parameter in the cart orders registration to false then add the menu in here using the following code
    //add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Orders', 'rapaygo-wp-plugin'),  __('Orders', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, 'edit.php?post_type=wpsc_cart_orders');
    //add_submenu_page(RAPAYGO_CART_MAIN_MENU_SLUG, __('Add Order', 'rapaygo-wp-plugin'),  __('Add Order', 'rapaygo-wp-plugin') , RAPAYGO_CART_MANAGEMENT_PERMISSION, 'post-new.php?post_type=wpsc_cart_orders');
    
    //TODO - Remove this at a later version. The purpose of this is to still keep the old setting link that will get redirected to the new settings menu.
    add_options_page(__("Rapaygo Shopping Cart", "rapaygo-wp-plugin"), __("Rapaygo Shopping Cart", "rapaygo-wp-plugin"), RAPAYGO_CART_MANAGEMENT_PERMISSION, 'wordpress-paypal-shopping-cart', 'rapaygo_settings_interface');
    
    $menu_parent_slug = RAPAYGO_CART_MAIN_MENU_SLUG;
    do_action('rapaygo_after_main_admin_menu', $menu_parent_slug);
}

/*
 * Main settings menu (it links to all other settings menu tabs). 
 * Only admin user with "manage_options" permission can access this menu page.
 */

function rapaygo_settings_interface() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    $rapaygo_plugin_tabs = array(
        'rapaygo-menu-main' => __('General Settings', 'rapaygo-wp-plugin'),
        'rapaygo-menu-main&action=email-settings' => __('Email Settings', 'rapaygo-wp-plugin'),
        'rapaygo-menu-main&action=adv-settings' => __('Advanced Settings', 'rapaygo-wp-plugin'),
    );
    echo '<div class="wrap">';
    echo '<h1>' . (__("Rapaygo Shopping Cart Options", "rapaygo-wp-plugin")) . ' v'.RAPAYGO_CART_VERSION . '</h1>';

    $current = "";
    if (isset($_GET['page'])) {
        $current = sanitize_text_field($_GET['page']);
        if (isset($_GET['action'])) {
            $current .= "&action=" . sanitize_text_field($_GET['action']);
        }
    }
    $content = '';
    $content .= '<h2 class="nav-tab-wrapper">';
    foreach ($rapaygo_plugin_tabs as $location => $tabname) {
        if ($current == $location) {
            $class = ' nav-tab-active';
        } else {
            $class = '';
        }
        $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
    }
    $content .= '</h2>';
    echo $content;
    echo '<div id="poststuff"><div id="post-body">';
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'email-settings':
                include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_email_settings.php');
                show_rapaygo_cart_email_settings_page();
                break;
	    case 'adv-settings':
                include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_adv_settings.php');
                show_rapaygo_cart_adv_settings_page();
                break;
        }
    } else {
        include_once (RAPAYGO_CART_PATH . 'includes/admin/rapaygo_menu_general_settings.php');
        rapaygo_show_general_settings_page();
    }
    echo '</div></div>';
    echo '</div>';
}

