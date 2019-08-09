/*
 * Logic for payment method "PayPal"
 */
Mealz.prototype.enablePaypal = function () {
    var amountField = $('#ecash_amount');

    // Clear the input field, when clicking on it
    amountField.click(function () {
        amountField.val('');
    });

    // Only render the button, when PayPal is chosen as the payment method
    if ($('#ecash_paymethod_0').attr("checked", "checked") && $('.paypal-buttons').length === 0) {
        paypalButtonRender();
    }

    /*
     * PayPal button rendering
     */
    function paypalButtonRender() {
        var invalidAmountMessage = $('.invalid-amount');

        paypal.Buttons({

            // Form validation
            onInit: function (data, actions) {
                if (amountField[0].checkValidity()  && parseFloat(amountField[0].value.replace(/,/g, '.')) > 0.00) {
                    actions.enable();
                } else {
                    actions.disable();
                }

                amountField.change(function () {
                    if (amountField[0].checkValidity() && parseFloat(amountField[0].value.replace(/,/g, '.')) > 0.00) {
                        actions.enable();
                        invalidAmountMessage.hide();
                    } else {
                        actions.disable();
                        invalidAmountMessage.show();
                    }
                });

            },

            onClick: function () {
                if (amountField[0].checkValidity() === false) {
                    invalidAmountMessage.show();
                }
            },

            // Set up the transaction
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: formatCurrency(amountField.val())
                        }
                    }]
                });
            },

            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    $('#ecash_orderid').val(data.orderID);

                    return fetch('/payment/ecash/form/submit', {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        },
                        body: JSON.stringify(
                           $('form[name="ecash"]').serializeArray()
                        )
                    });
                });
            }

        }).render('#paypal-button-container')
    }


    /*
     * Format amount input field
     */
    function formatCurrency(total) {
        var neg = false;
        if (total < 0) {
            neg = true;
            total = Math.abs(total);
        }
        return (neg ? "-" : '') + parseFloat(total, 10).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString();
    }
};
