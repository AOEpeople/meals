    /**
    * Paypal JS-Logic
    */

Mealz.prototype.enablePaypal = function () {
    /**
    * As long only PayPal is available - let condition outcommented

    $('#ecash_paymethod').click(function(){
        if ($('#ecash_paymethod').children('input').val() === "0" && $('.paypal-buttons').length === 0){
            paypalButtonRender();
        }
    });*/

    if ($('#ecash_paymethod_0').attr("checked", "checked") && $('.paypal-buttons').length === 0) {
        paypalButtonRender();
    }

    /**
    * paypal Button Render Function
    */
    function paypalButtonRender(){
        paypal.Buttons({
        createOrder: function(data, actions) {
            // Set up the transaction
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: formatCurrency($('#ecash_amount').val())
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                alert('Transaction completed by ' + details.payer.name.given_name);
                    // Call your server to save the transaction
                    return fetch('/paypal-transaction-complete', {
                        method: 'post',
                        headers: {
                        'content-type': 'application/json'
                    },
                        body: JSON.stringify({
                        orderID: data.orderID
                    })
                });
            });
        }
        }).render('#paypal-button-container')
    }

    /**
    * Format Currency
    */
    function formatCurrency(total) {
        var neg = false;
        if(total < 0) {
            neg = true;
            total = Math.abs(total);
        }
        return (neg ? "-" : '') + parseFloat(total, 10).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString();
    }
};
