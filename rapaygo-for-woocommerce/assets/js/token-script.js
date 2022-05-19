var successCallback = function(data) {

	console.log("successCallback", data);
	// $( 'form.woocommerce-checkout' ).find('#ybc_token').val(data.token);
	// $( 'form.woocommerce-checkout' ).off( 'checkout_place_order', tokenRequest );
	// $( 'form.woocommerce-checkout' ).submit();

};

var errorCallback = function(data) {
    console.log(data);
};

var processAllData = function() {

	// here will be a payment gateway function that process all the card data from your form and call the success and failure function depending on the response,
	console.log(processAllData);
	return false;
		
};

jQuery(function($){

	$( 'form.woocommerce-checkout' ).on( 'checkout_place_order', processAllData );

});