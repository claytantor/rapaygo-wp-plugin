<?php
class rapaygo_Coupons_Collection
{
    var $coupon_items = array();

    function __construct()
    {

    }

    function add_coupon_item($coupon_item)
    {
        array_push($this->coupon_items, $coupon_item);
    }

    function find_coupon_by_code($coupon_code)
    {
        if(empty($this->coupon_items)){
            echo "<br />".(__("Admin needs to configure some discount coupons before it can be used", "rapaygo-wp-plugin"));
            return new stdClass();
        }
        foreach($this->coupon_items as $key => $coupon)
        {
            if(strtolower($coupon->coupon_code) == strtolower($coupon_code)){
                return $coupon;
            }
        }
        return new stdClass();
    }

    function delete_coupon_item_by_id($coupon_id)
    {
        $coupon_deleted = false;
        foreach($this->coupon_items as $key => $coupon)
        {
            if($coupon->id == $coupon_id){
                $coupon_deleted = true;
                unset($this->coupon_items[$key]);
            }
        }
        if($coupon_deleted){
            $this->coupon_items = array_values($this->coupon_items);
            rapaygo_Coupons_Collection::save_object($this);
        }
    }

    function print_coupons_collection()
    {
        foreach ($this->coupon_items as $item){
            $item->print_coupon_item_details();
        }
    }

    static function save_object($obj_to_save)
    {
        update_option('rapaygo_coupons_collection', $obj_to_save);
    }

    static function get_instance()
    {
        $obj = get_option('rapaygo_coupons_collection');
        if($obj){
            return $obj;
        }else{
            return new rapaygo_Coupons_Collection();
        }
    }
}

class rapaygo_COUPON_ITEM
{
    var $id;
    var $coupon_code;
    var $discount_rate;
    var $expiry_date;
    function __construct($coupon_code, $discount_rate, $expiry_date)
    {
        $this->id = uniqid();
        $this->coupon_code = $coupon_code;
        $this->discount_rate = $discount_rate;
        $this->expiry_date = $expiry_date;
    }

    function print_coupon_item_details()
    {
        echo "<br />".(__("Coupon ID: ", "rapaygo-wp-plugin")).$this->id;
        echo "<br />".(__("Coupon Code: ", "rapaygo-wp-plugin")).$this->coupon_code;
        echo "<br />".(__("Discount Amt: ", "rapaygo-wp-plugin")).$this->discount_rate;
        echo "<br />".(__("Expiry date: ", "rapaygo-wp-plugin")).$this->expiry_date;
    }
}

function rapaygo_apply_cart_discount($coupon_code)
{
    $collection_obj = rapaygo_Coupons_Collection::get_instance();
    $coupon_item = $collection_obj->find_coupon_by_code($coupon_code);
    if(!isset($coupon_item->id)){
        $_SESSION['rapaygo_cart_action_msg'] = '<div class="rapaygo_error_message">'.__("Coupon code used does not exist!", "rapaygo-wp-plugin").'</div>';
        return;
    }
    $coupon_expiry_date = $coupon_item->expiry_date;
    if(!empty($coupon_expiry_date)){
        $current_date = date("Y-m-d");
        if($current_date > $coupon_expiry_date){
            $_SESSION['rapaygo_cart_action_msg'] = '<div class="rapaygo_error_message">'.__("Coupon code expired!", "rapaygo-wp-plugin").'</div>';
            return;
        }
    }
    if (isset($_SESSION['rapaygo_discount_applied_once']) && $_SESSION['rapaygo_discount_applied_once'] == '1'){
        $_SESSION['rapaygo_cart_action_msg'] = '<div class="rapaygo_error_message">'.__("Discount can only be applied once per checkout!", "rapaygo-wp-plugin").'</div>';
        return;
    }

    //Apply the discount
    $curr_symbol = WP_CART_CURRENCY_SYMBOL;
    $discount_rate = $coupon_item->discount_rate;
    $products = $_SESSION['simpleCart'];
    $discount_total = 0;
    foreach ($products as $key => $item)
    {
        if ($item['price'] > 0)
        {
            $item_discount = (($item['price_orig']*$discount_rate)/100);
            $discount_total = $discount_total + $item_discount*$item['quantity'];
            $item['price'] = round(($item['price_orig'] - $item_discount), 2);
            unset($products[$key]);
            array_push($products, $item);
        }
    }
    $_SESSION['simpleCart'] = $products;
    $disct_amt_msg = print_payment_currency($discount_total, $curr_symbol);
    $_SESSION['rapaygo_cart_action_msg'] = '<div class="rapaygo_success_message">'.__("Discount applied successfully! Total Discount: ", "rapaygo-wp-plugin").$disct_amt_msg.'</div>';
    $_SESSION['rapaygo_discount_applied_once'] = '1';
    $_SESSION['rapaygo_applied_coupon_code'] = $coupon_code;
}

function rapaygo_reapply_discount_coupon_if_needed()
{
    //Re-apply coupon to the cart if necessary (meaning a coupon was already applied to the cart when this item was modified.
    if (isset($_SESSION['rapaygo_discount_applied_once']) && $_SESSION['rapaygo_discount_applied_once'] == '1'){
        $coupon_code = $_SESSION['rapaygo_applied_coupon_code'];
        unset($_SESSION['rapaygo_discount_applied_once']);
        rapaygo_apply_cart_discount($coupon_code);
    }
}