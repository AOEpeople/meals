<template>
  <div class="flex m-8 justify-center">
    <h5 class="w-32 m-0 self-center text-black">{{ t('balance.amount') }}: €</h5>
    <input
        type="text"
        :value="balance < 0 ? balance.toFixed(2).slice(1).replace(/\./g, ',') : '0,00'"
        ref="input"
        class="bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12 text-center"
    />
  </div>

  <div id="paypal-container" class="mx-8 my-2"></div>
</template>

<script>
import { loadScript } from "@paypal/paypal-js";

export default {
  data() {
    return {
      balance: parseFloat(sessionStorage.getItem('balance'))
    }
  },
  async mounted() {
    let amountField = this.$refs['input'];

    function formatCurrency(total) {
      let neg = false;
      if (total < 0) {
        neg = true;
        total = Math.abs(total);
      }
      return (neg ? '-' : '') + parseFloat(total.replace(/,/g, '.')).toFixed(2).toString();
    }

    loadScript({
      "client-id": process.env.IDP_CLIENT_ID,
      currency: "EUR",
    })
        .then((paypal) => {
          paypal
              .Buttons({
                onInit: function (data, actions) {
                  if (amountField.checkValidity() === true && parseFloat(amountField.value.replace(/,/g, '.')) > 0.00) {
                    actions.enable();
                  } else {
                    actions.disable();
                  }

                  amountField.addEventListener('input', () => {
                    // Replace a comma with a point and parse the input string to a float
                    let amountFieldValue = parseFloat(amountField.value.replace(/,/g, '.'));

                    amountField.setCustomValidity('');

                    // If the input is valid (matches the HTML pattern: "\d*([.,]?\d{0,2})") and the value is above 0.00..
                    if (amountField.checkValidity() === true && amountFieldValue > 0.00) {

                      // Enable PayPal buttons and remove the warning message.
                      actions.enable();
                    } else {
                      actions.disable();
                      amountField.setCustomValidity('Invalid field');
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
                    return fetch('/payment/ecash/form/submit', {
                      method: 'post',
                      headers: {
                        'content-type': 'application/json'
                      },
                      body: JSON.stringify([
                        { 'name': 'ecash[profile]', 'value': sessionStorage.getItem('user') },
                        { 'name': 'ecash[orderid]', 'value': data.orderID },
                        { 'name': 'ecash[amount]', 'value': formatCurrency(amountField.value) }
                      ])
                    }).then((response) => {
                      console.log(response)
                    }).then((redirectPath) => {
                      console.log(redirectPath);
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
  }
}
</script>

<script setup>
import { useI18n } from "vue-i18n";
const { t } = useI18n();
</script>
