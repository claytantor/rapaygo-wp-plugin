<?php

function rapaygo_cart_add_tinymce_button() {

    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
	return;
    }

    // Add only in Rich Editor mode
    if ( get_user_option( 'rich_editing' ) == 'true' ) {

	add_action( 'admin_print_scripts', 'rapaygo_cart_print_admin_scripts' );
	add_action( 'wp_ajax_rapaygo_cart_get_tinymce_form', 'rapaygo_cart_tinymce_ajax_handler' ); // Add ajax action handler for tinymce
	add_filter( 'mce_external_plugins', "rapaygo_cart_add_tinymce_plugin", 5 );
	add_filter( 'mce_buttons', 'rapaygo_cart_register_button', 5 );

	// Required by TinyMCE button
//        add_action('wp_ajax_orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content', 'orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content');
//        add_action('wp_ajax_orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content', 'orbsius_ui_for_paypal_shopping_cart_ajax_render_popup_content');
    }
}

function rapaygo_cart_add_tinymce_plugin( $plugin_array ) {
    $plugin_array[ 'rapaygo_cart_shortcode' ] = RAPAYGO_CART_URL . '/assets/js/tinymce/rapaygo_plugin.js';
    return $plugin_array;
}

function rapaygo_cart_register_button( $buttons ) {
    $buttons[] = 'rapaygo_cart_shortcode';
    return $buttons;
}

function rapaygo_cart_print_admin_scripts() {
    //The following is used by the TinyMCE button.
    ?>
    <script type="text/javascript">
        var rapaygo_cart_admin_ajax_url = '<?php echo admin_url( 'admin-ajax.php?action=ajax' ); ?>';
    </script>
    <?php
}

function rapaygo_cart_tinymce_ajax_handler() {
    ?>
    <style>
        #TB_window, #TB_ajaxContent {height: auto !important}
        .mceActionPanel {padding: 20px; margin-top: 10px;    border-top: 1px solid silver;}
    </style>
    <script>
        function ui_for_ppsc_insert_content() {
    	var extra = '';
    	var content;
    	var template = '<p>[rapaygo_cart_button name="%%PRODUCT-NAME%%" price="%%PRODUCT-PRICE%%"%%EXTRA%%]</p>';

    	var wpsppsc = document.getElementById('wpsppsc_panel');

    	var product_name = document.getElementById('wpsppsc_product_name').value;
    	var product_price = document.getElementById('wpsppsc_product_price').value;
    	var shipping = document.getElementById('wpsppsc_shipping').value;
    	var file_url = document.getElementById('wpsppsc_file_url').value;

    	var custom1_id = document.getElementById('wpsppsc_custom1_id').value;
    	var custom1_vals = document.getElementById('wpsppsc_custom1_values').value;

    	var custom2_id = document.getElementById('wpsppsc_custom2_id').value;
    	var custom2_vals = document.getElementById('wpsppsc_custom2_values').value;

    	var custom3_id = document.getElementById('wpsppsc_custom3_id').value;
    	var custom3_vals = document.getElementById('wpsppsc_custom3_values').value;

    	var seq = 1; // Shopping cart needs VAR1, VAR2 etc.

    	// who is active ?
    	if (wpsppsc.className.indexOf('current') != -1) {
    	    product_name = product_name.replace(/</g, '').replace(/\n/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '').replace(/:+/g, '-');
    	    product_price = product_price.replace(/[^\d-.]/g, '');
    	    shipping = shipping.replace(/[^\d-.]/gi, '');
    	    //file_url = file_url.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');

    	    custom1_id = custom1_id.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');
    	    custom1_vals = custom1_vals.replace(/[<>\r\n:]+/gi, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|');

    	    custom2_id = custom2_id.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');
    	    custom2_vals = custom2_vals.replace(/[<>\r\n:]+/gi, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|');

    	    custom3_id = custom3_id.replace(/[<>\r\n:]+/g, '').replace(/^\s*/g, '').replace(/\s*$/g, '');
    	    custom3_vals = custom3_vals.replace(/[<>\r\n:]+/gi, '').replace(/^[\s,]*/g, '').replace(/[\s,]*$/g, '').replace(/\s*,+\s*/g, '|');

    	    // Validations
    	    if (product_name == '') {
    		alert('<?php _e( "Please enter product name", 'rapaygo-wp-plugin' ); ?>');
    		document.getElementById('wpsppsc_product_name').focus();
    		return false;
    	    }

    	    product_price = product_price || 0;

    	    if (product_price == 0) {
    		alert('<?php _e( "Please enter product price", 'rapaygo-wp-plugin' ); ?>');
    		document.getElementById('wpsppsc_product_price').focus();
    		return false;
    	    }

    	    shipping = shipping || 0;

    	    if (shipping) {
    		extra += ' shipping="' + shipping + '"';
    	    }

    	    //File URL
    	    if (file_url) {
    		extra += ' file_url="' + file_url + '"';
    	    }

    	    //Product Variations. Example custom1_id: Format | custom1_vals: PAL, Secam
    	    if (custom1_id) {
    		extra += ' var' + seq + '="' + custom1_id + '|' + custom1_vals + '"';
    		seq++;
    	    }

    	    if (custom2_id) {
    		extra += ' var' + seq + '="' + custom2_id + '|' + custom2_vals + '"';
    		seq++;
    	    }

    	    if (custom3_id) {
    		extra += ' var' + seq + '="' + custom3_id + '|' + custom3_vals + '"';
    		seq++;
    	    }

    	    content = template;
    	    content = content.replace(/%%PRODUCT-NAME%%/ig, product_name).replace(/%%PRODUCT-PRICE%%/ig, product_price);
    	    content = content.replace(/%%EXTRA%%/ig, extra);
    	}

    	parent.tinyMCE.execCommand('mceInsertContent', false, content);

    	tb_remove();

    	return false;
        }
    </script>
    <form name="wpsppsc_form" action="#">
        <div class="panel_wrapper">
    	<!-- panel -->
    	<div id="wpsppsc_panel" class="panel current">

    	    <p><?php _e( sprintf( 'Visit the %s page to learn all the shortcode usage.', '<a href="https://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart" target="_blank">' . __( 'Simple Cart Documentation', 'rapaygo-wp-plugin' ) . '</a>' ), 'rapaygo-wp-plugin' ); ?></p>
    	    <br />

    	    <table border="0" cellpadding="4" cellspacing="0">
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_product_name"><?php _e( "Product Name", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td>
    			<input type="text" id="wpsppsc_product_name" name="wpsppsc_product_name" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Example: My Great Product", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_product_price"><?php _e( "Price", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td>
    			<input type="text" id="wpsppsc_product_price" name="wpsppsc_product_price" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Example: 10 or 10.50", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_shipping"><?php _e( "Shipping (Optional)", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td>
    			<input type="text" id="wpsppsc_shipping" name="wpsppsc_shipping" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Example: 10 or 10.50", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_file_url"><?php _e( "File URL (Optional)", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td>
    			<input type="text" id="wpsppsc_file_url" name="wpsppsc_shipping" value="" />
    		    </td>
    		    <td>
			    <?php _e( 'Example:' ); ?> http://www.your-site.com/wp-content/uploads/my-ebook.zip
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap" colspan="3">
    			<br/>
    			<strong><?php _e( "Product Variations (Optional)", 'rapaygo-wp-plugin' ); ?></strong>
    			<p><?php _e( 'Example: For a T-Shirt product you may want to use a variation with name "Size" and values as "Small, Medium, Large"', 'rapaygo-wp-plugin' ); ?></p>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_custom1_id"><?php _e( "Variation 1: Name", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td><input type="text" id="wpsppsc_custom1_id" name="wpsppsc_custom1_id" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Values", 'rapaygo-wp-plugin' ); ?>
    			<input type="text" id="wpsppsc_custom1_values" name="wpsppsc_custom1_values" value="" /><?php _e(" Example: Small, Medium, Large", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_custom2_id"><?php _e( "Variation 2: Name", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td><input type="text" id="wpsppsc_custom2_id" name="wpsppsc_custom2_id" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Values", 'rapaygo-wp-plugin' ); ?>
    			<input type="text" id="wpsppsc_custom2_values" name="wpsppsc_custom2_values" value="" /><?php _e(" Example: Blue, Red, Black, White", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    		<tr>
    		    <td nowrap="nowrap">
    			<label for="wpsppsc_custom3_id"><?php _e( "Variation 3: Name", 'rapaygo-wp-plugin' ); ?></label>
    		    </td>
    		    <td><input type="text" id="wpsppsc_custom3_id" name="wpsppsc_custom3_id" value="" />
    		    </td>
    		    <td>
			    <?php _e( "Values", 'rapaygo-wp-plugin' ); ?>
    			<input type="text" id="wpsppsc_custom3_values" name="wpsppsc_custom3_values" value="" /><?php _e(" Example: Short, Full", 'rapaygo-wp-plugin' ); ?>
    		    </td>
    		</tr>
    	    </table>
    	</div>
    	<!-- end panel -->

    	<div class="mceActionPanel">

    	    <div style="float: left">
    		<input type="submit" id="insert" name="insert" value="<?php _e( "Insert", 'rapaygo-wp-plugin' ); ?>"
    		       class='app_positive_button  mceButton button-primary'
    		       onclick="ui_for_ppsc_insert_content();
    			       return false;" />
    	    </div>

    	    <div style="float: right">
    		<input type="button" id="cancel" name="cancel" value="<?php _e( "Cancel", 'rapaygo-wp-plugin' ); ?>"
    		       class='app_negative_button button'
    		       onclick="tb_remove();" />
    	    </div>

    	    <br />
    	</div>
        </div>
    </form>
    <?php
    die();
}
