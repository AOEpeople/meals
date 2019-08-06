    /**
    * Paypal JS-Logic
    */

Mealz.prototype.enablepaypal = function () {
    /**
    * As long only PayPal is available - let condition outcommented
    */
    //$('#ecash_paymethod').click(function(){
        if ($('#ecash_paymethod').children('input').val() === "0" && $('.paypal-buttons').length === 0){
            paypal.Buttons().render('#paypal-button-container')
        }
    //});

};
