<template>
  <div id="paypal-container"></div>
</template>

<script setup>
import {onMounted} from "vue";
import {loadScript} from "@paypal/paypal-js";

onMounted(async () => {
  let instance = loadScript({
    "client-id": "Acbj_OdOFasvxF6I2mJrrQTMN3vuSE65WbnyRkBBCMF5U32g63sXbCbPflPDA8sMDttBUsbLW7r59OtE",
    currency: "EUR",
  })
    .then((paypal) => {
      paypal
        .Buttons({
          onInit: function (data, actions) {
            isNaN(props.amount) ? actions.disable() : actions.enable();
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
                if (redirect.status === 200 && redirect.redirected === false) {
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
                  value: formatCurrency(amountField.val())
                }
              }]
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

</script>
