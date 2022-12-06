<template>
  <radar-spinner
    v-if="!loaded"
    :animation-duration="2000"
    :size="60"
    color="#1b5298"
  />
  <div v-show="loaded">
    <div class="m-8 flex justify-center">
      <h5 class="m-0 w-32 self-center text-black">
        {{ t('balance.amount') }}: €
      </h5>
      <input
        ref="input"
        type="text"
        :value="balance < 0 ? balance.toFixed(2).slice(1).replace(/\./g, ',') : '0,00'"
        class="bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12 text-center"
      >
    </div>
    <div
      id="paypal-container"
      class="mx-8 my-2"
    />
  </div>
</template>

<script>
import { RadarSpinner } from 'epic-spinners'
import { loadScript } from "@paypal/paypal-js";
import { balanceStore } from "@/stores/balanceStore";
import { transactionStore } from "@/stores/transactionStore";
import { useI18n } from "vue-i18n";
import {ref} from "vue";
import {usePaypal} from "@/api/getPaypal";

export default {
  components: {RadarSpinner},
  emits: ['closePanel'],
  setup() {
    const { t } = useI18n();
    return { t };
  },
  data() {
    return {
      balance: parseFloat(sessionStorage.getItem('balance')),
      locale: ref('en'),
      loaded: ref(false)
    }
  },
  async mounted() {
    let amountField = this.$refs['input'];
    const vm = this;

    const { locale } = useI18n();

    const { id } = await usePaypal()

    function formatCurrency(total) {
      let neg = false;
      if (total < 0) {
        neg = true;
        total = Math.abs(total);
      }
      return (neg ? '-' : '') + parseFloat(total.replace(/,/g, '.')).toFixed(2).toString();
    }

    loadScript({
      "client-id": id,
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

                      if (locale.value === 'en') {

                        // ..add missing decimal places and render the amount in the point format.
                        amountField.value = amountFieldValue.toFixed(2);

                        // If the language is set to German.
                      } else {
                        // ..add missing decimal places and render the amount in the comma format.
                        amountField.value = amountFieldValue.toFixed(2).replace(/\./g, ',');
                      }

                      // Enable PayPal buttons and remove the warning message.
                      actions.enable();
                    } else {
                      actions.disable();
                      amountField.setCustomValidity('Invalid field');
                    }
                  });
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
                      if(response.ok) {
                        balanceStore.adjustAmount(parseFloat(formatCurrency(amountField.value)));
                        transactionStore.fillStore();
                        vm.$emit('closePanel');
                      }
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
        })
        .then(() => this.loaded = true);
  }
}

</script>
