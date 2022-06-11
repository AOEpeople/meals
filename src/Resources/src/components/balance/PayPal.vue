<template>
  <div id="paypal-container"></div>
</template>

<script setup>
import {onCreated} from "vue";
import {loadScript} from "@paypal/paypal-js";

const props = defineProps([
    'input'
]);

onCreated(async () => {

  let amountField = props.input;

  loadScript({
    "client-id": "Acbj_OdOFasvxF6I2mJrrQTMN3vuSE65WbnyRkBBCMF5U32g63sXbCbPflPDA8sMDttBUsbLW7r59OtE",
    currency: "EUR",
  })
    .then((paypal) => {
      paypal
        .Buttons({
          onInit: function (data, actions) {
            if (amountField[0].checkValidity() === true && parseFloat(amountField.value.replace(/,/g, '.')) > 0.00) {
              actions.enable();
            } else {
              actions.disable();
            }

            amountField.change(function () {
              // Replace a comma with a point and parse the input string to a float
              var amountFieldValue = parseFloat(amountField.value.replace(/,/g, '.'));

              amountField[0].setCustomValidity('');

              // If the input is valid (matches the HTML pattern: "\d*([.,]?\d{0,2})") and the value is above 0.00..
              if (amountField[0].checkValidity() === true && amountFieldValue > 0.00) {

                // Enable PayPal buttons and remove the warning message.
                actions.enable();
              } else {
                actions.disable();
                amountField[0].setCustomValidity('Invalid field');
              }
            });

          },
          onError: function (err) {
            // if its the "Window navigated away" Error which always happens caused by redirect - ignore it
            if (err.text() !== 'Window navigated away') {
              return fetch('/payment/ecash/form/submit', {
                method: 'post',
                headers: {
                  'content-type': 'application/json'
                },
              }).then(function (redirect) {
                if (redirect.status === 200 && !redirect.redirected) {
                  return (redirect.text());
                }
              }).then(function (redirect) {
                actions.redirect(window.location.origin + redirect);
              });
            }
          },
          // Set up the transaction
          createOrder: function (data, actions) {
            return actions.order.create({
              purchase_units: [{
                amount: {
                  value: formatCurrency(amountField.value)
                }
              }]
            });
          },
          onApprove: function (data, actions) {
            return actions.order.capture().then(() => {
              $('#ecash_orderid').val(data.orderID);
              return fetch('/payment/ecash/form/submit', {
                method: 'post',
                headers: {
                  'content-type': 'application/json'
                },
                body: [
                  { 'name': 'ecash[profile]', 'value': sessionStorage.getItem('user') },
                  { 'name': 'ecash[orderid]', 'value': data.orderID },
                  { 'name': 'ecash[amount]', 'value': formatCurrency(amountField.value) }
                ]
              }).then(function (response) {
                if (response.status !== 200) {
                  return '/payment/ecash/transaction/failure';
                }
                if (response.status === 200 && !response.redirected) {
                  return (response.text());
                }
              }).then(function (redirectPath) {
                return actions.redirect(window.location.origin + redirectPath);
              });
            });
          },
        })
        .render("#paypal-container")
        .catch((error) => {
          console.error("failed to render the PayPal Buttons", error);
        });
    })
    .catch((error) => {
      console.error("failed to load the PayPal JS SDK script", error);
    });
});

    /*
     * Format amount input field
     */
function formatCurrency(total) {
  var neg = false;
  if (total < 0) {
    neg = true;
    total = Math.abs(total);
  }
  return (neg ? '-' : '') + parseFloat(total.replace(/,/g, '.')).toFixed(2).toString();
}
</script>
