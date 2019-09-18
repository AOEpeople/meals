/*
 * Logic for payment method "PayPal"
 */
Mealz.prototype.enablePaypal = function () {
    var amountField = $('#ecash_amount');

    // Disable usage of "Enter" button in the input field to avoid POST request of form
    amountField.on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
        }
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
                if (amountField[0].checkValidity() === true && parseFloat(amountField.val().replace(/,/g, '.')) > 0.00) {
                    actions.enable();
                } else {
                    actions.disable();
                }

                amountField.change(function () {
                    // Replace a comma with a point and parse the input string to a float
                    var amountFieldValue = parseFloat(amountField.val().replace(/,/g, '.'));

                    amountField[0].setCustomValidity("");

                    // If the input is valid (matches the HTML pattern: "\d*([.,]?\d{0,2})") and the value is above 0.00..
                    if (amountField[0].checkValidity() === true && amountFieldValue > 0.00) {

                        // ..and the language is set to German..
                        if ($('.language-switch').find('span').text() === 'de') {

                            // ..add missing decimal places and render the amount in the comma format.
                            amountField.val(amountFieldValue.toFixed(2).replace(/\./g, ','));

                            // If the language is set to English..
                        } else {

                            // ..add missing decimal places and render the amount in the point format.
                            amountField.val(amountFieldValue.toFixed(2));
                        }

                        // Enable PayPal buttons and remove the warning message.
                        actions.enable();
                        invalidAmountMessage.hide();
                    } else {
                        actions.disable();
                        invalidAmountMessage.show();
                        amountField[0].setCustomValidity("Invalid field");
                    }
                });

            },

            onClick: function () {
                if (amountField[0].checkValidity() === false || parseFloat(amountField[0].value.replace(/,/g, '.')) <= 0.00) {
                    invalidAmountMessage.show();
                    amountField[0].setCustomValidity("Invalid field");
                }
            },

            onCancel: function (data, actions) {
                window.location.reload();
            },

            onError: function (err) {
                // if its the "Window navigated away" Error which always happens caused by redirect - ignore it
                if(err.text() !== 'Window navigated away') {
                    return fetch('/payment/ecash/form/submit', {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        },
                    }).then(function (redirect) {
                        if (redirect.status === 200 && redirect.redirected === false) {
                            return (redirect.text());
                        }
                    }).then(function (redirect) {
                        actions.redirect(window.location.origin+redirect);
                    });
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

            onApprove: function (data, actions) {
                $('body').prepend('<div class="cover"></div>');

                return actions.order.capture().then(function (details) {
                    $('#ecash_orderid').val(data.orderID);
                    return fetch('/payment/ecash/form/submit', {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        },
                        body: JSON.stringify(
                            $('form[name="ecash"]').serializeArray()
                        )
                    }).then(function (redirect) {
                        if (redirect.status === 200 && redirect.redirected === false) {
                            return (redirect.text());
                        }
                    }).then(function (redirect) {
                        return actions.redirect(window.location.origin+redirect);
                    });
                });
            }
        }).render('.paypal-button-container');
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
        return (neg ? "-" : '') + parseFloat(total.replace(/,/g, '.')).toFixed(2).toString();
    }

};
