<template>
  <radar-spinner
    v-if="!loaded"
    :animation-duration="2000"
    :size="60"
    color="#1b5298"
  />
  <div
    v-show="loaded"
    class="w-[300px] p-2 text-center align-middle sm:w-[420px]"
  >
    <div class="mb-8 flex justify-center gap-1">
      <h5 class="m-0 self-center text-[14px] text-black sm:text-[18px]">
        {{ t('balance.amount') }}: €
      </h5>
      <!-- <input
        ref="amountField"
        type="text"
        :value="balance < 0 ? balance.toFixed(2).slice(1).replace(/\./g, ',') : '0,00'"
        class="h-[46px] rounded-full border-[2px] border-solid border-[#CAD6E1] bg-white text-center"
      > -->
      <MoneyInput
        v-model="amountFieldValue"
      />
    </div>
    <div
      id="paypal-container"
      class="mx-8 my-2"
    />
  </div>
</template>

<script setup lang="ts">
import { RadarSpinner } from 'epic-spinners'
import { loadScript } from "@paypal/paypal-js";
import { transactionStore } from "@/stores/transactionStore";
import { useI18n } from "vue-i18n";
import { WatchStopHandle, computed, onMounted, onUnmounted, ref, watch } from "vue";
import { userDataStore } from "@/stores/userDataStore";
import { environmentStore } from "@/stores/environmentStore";
import postPaypalOrder from '@/api/postPaypalOrder';
import MoneyInput from '../misc/MoneyInput.vue';

const { t, locale } = useI18n();

const balance = computed(() => userDataStore.getState().balance);
// const amountField = ref<HTMLInputElement | null>(null);
const amountFieldValue = ref(Math.abs(balance.value));
const amountFieldStrRepr = computed(() => {
  console.log(`Before conversion: ${amountFieldValue.value}`);
  const amount = amountFieldValue.value.toFixed(2);
  console.log(`After conversion: ${amount}`);
  return locale.value === 'en' ? amountFieldValue.value.toFixed(2) : amountFieldValue.value.toFixed(2).replace(/\./, ',');
});
const loaded = ref(false);
const actionsWatcher = ref<WatchStopHandle | null>(null);

const emit = defineEmits(['closePanel']);

watch(
 () => amountFieldStrRepr.value,
 () => console.log(`amount changed to ${amountFieldStrRepr.value}`)
);

onMounted(async () => {
  console.log('Mounted Paypal');
  try {
    const paypal = await loadScript({
      'client-id': environmentStore.getState().paypalId,
      currency: 'EUR'
    });
    console.log('loaded paypal script');

    paypal.Buttons({
      onInit: function(data, actions) {
        if (amountFieldValue.value > 0.00) {
          actions.enable();
          console.log('Actions enabled initially');
        } else {
          actions.disable();
          console.log('Actions disabled initially');
        }

        actionsWatcher.value = watch(
          () => amountFieldValue.value,
          () => {
            if (amountFieldValue.value > 0.00) {
              // amountField.value.value = amountFieldStrRepr.value;
              actions.enable();
              console.log('Actions enabled with watch');
            } else {
              actions.disable();
              console.log('Actions disabled with watch');
            }
          }
        );
      },
      createOrder: function(data, actions) {
        console.log(`Creating Order with amount: ${amountFieldValue.value}`);
        return actions.order.create({
          purchase_units: [{
            amount: {
              value: formatCurrency(amountFieldValue.value)
            }
          }]
        });
      },
      onApprove: function (data, actions) {
        emit('closePanel');
        console.log('order was Approved');
        return actions.order.capture().then(async () => {

          const { error, response } = await postPaypalOrder(amountFieldValue.value.toFixed(2), data.orderID);

          if(error.value === false) {
            console.log('No errors from backend');
            userDataStore.adjustBalance(parseFloat(formatCurrency(amountFieldValue.value)));
            transactionStore.fillStore();
            // disable gray out and show spinner
          } else {
            console.log('Error from Backend');
          }

        });
      }
    })
    .render('#paypal-container')
    .catch(error => console.error('failed to render the PayPal Buttons', error));

  } catch(error) {
    console.error('failed to load the PayPal JS SDK script', error);
  }
  loaded.value = true;
});

onUnmounted(() => {
  if (actionsWatcher.value !== null) {
    actionsWatcher.value();
  } else {
    console.log('ActionsWatcher not found!');
  }
})

function formatCurrency(total: number) {
  let neg = false;
  if (total < 0) {
    neg = true;
    total = Math.abs(total);
  }
  return (neg ? '-' : '') + total.toFixed(2);
}
</script>