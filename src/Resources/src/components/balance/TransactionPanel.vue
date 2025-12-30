<template>
  <BlockPopup :is-open="isLoading" />
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
      <h5 class="m-0 self-center text-[14px] text-black sm:text-[18px]">{{ t('balance.amount') }}: €</h5>
      <MoneyInput v-model="amountFieldValue" />
    </div>
    <div
      id="paypal-container"
      class="mx-8 my-2"
    />
  </div>
</template>

<script setup lang="ts">
import { RadarSpinner } from 'epic-spinners';
import { loadScript } from '@paypal/paypal-js';
import { transactionStore } from '@/stores/transactionStore';
import { useI18n } from 'vue-i18n';
import { type WatchStopHandle, computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { userDataStore } from '@/stores/userDataStore';
import { environmentStore } from '@/stores/environmentStore';
import postPaypalOrder from '@/api/postPaypalOrder';
import MoneyInput from '../misc/MoneyInput.vue';
import BlockPopup from '../misc/BlockPopup.vue';
import checkActiveSession from '@/tools/checkActiveSession';
import { usePeriodicFetch } from '@/services/usePeriodicFetch';
import useFlashMessage from '@/services/useFlashMessage';
import { useUserData } from '@/api/getUserData';

const KEEP_ALIVE_INTERVAL_MILLIS = 40000;

const { t } = useI18n();
const { periodicFetchActive } = usePeriodicFetch(KEEP_ALIVE_INTERVAL_MILLIS, () => checkActiveSession());

const balance = computed(() => userDataStore.getState().balance);
const amountFieldValue = ref(0.0);
const loaded = ref(false);
const isLoading = ref(false);
const actionsWatcher = ref<WatchStopHandle | null>(null);

const emit = defineEmits(['closePanel']);

onMounted(async () => {
  await checkActiveSession();
  amountFieldValue.value = balance.value < 0 ? Math.abs(balance.value) : 0.0;
  try {
    const paypal = await loadScript({
      clientId: environmentStore.getState().paypalId,
      currency: 'EUR'
    });

    if (paypal && paypal.Buttons) {
      let activeSessionIntervalId;
      paypal
        .Buttons({
          onInit: function (_data, actions) {
            periodicFetchActive.value = true;
            if (amountFieldValue.value > 0.0) {
              actions.enable();
            } else {
              actions.disable();
            }

            actionsWatcher.value = watch(
              () => amountFieldValue.value,
              () => {
                if (amountFieldValue.value > 0.0) {
                  actions.enable();
                } else {
                  actions.disable();
                }
              }
            );
          },
          createOrder: function (_data, actions) {
            activeSessionIntervalId = setInterval(
              () => {
                checkActiveSession();
              },
              10 * 60 * 1000
            );
            return actions.order.create({
              purchase_units: [
                {
                  amount: {
                    value: formatCurrency(amountFieldValue.value),
                    currency_code: 'EUR'
                  }
                }
              ],
              intent: 'CAPTURE'
            });
          },
          onShippingChange: function (_data, actions) {
            return actions.resolve();
          },
          onApprove: async function (data, actions) {
            clearInterval(activeSessionIntervalId);
            isLoading.value = true;
            try {
              await actions.order?.capture();

              checkActiveSession(
                JSON.stringify({
                  amount: amountFieldValue.value.toFixed(2),
                  orderId: data.orderID
                })
              );

              const response = await postPaypalOrder(amountFieldValue.value.toFixed(2), data.orderID);
              isLoading.value = false;
              if (!response.ok) {
                return;
              }

              userDataStore.adjustBalance(parseFloat(formatCurrency(amountFieldValue.value)));
              transactionStore.fillStore();
              const { userData } = await useUserData();
              const debtLimit = window.appData?.meals_locked_debt_limit as number;
              const currentBalance = userData?.value?.balance as number;
              if (currentBalance >= debtLimit) {
                useFlashMessage().removeMessagesByMessageCode('balanceBelowBalanceLimit');
              }

              // disable gray out and show spinner
              emit('closePanel');
            } catch (error) {
              isLoading.value = false;
            }
          }
        })
        .render('#paypal-container')
        .catch((error) => {
          console.error('failed to render the PayPal Buttons', error);
          isLoading.value = false;
        });
    } else {
      console.error('failed to load the PayPal JS SDK script');
      isLoading.value = false;
    }
  } catch (error) {
    console.error('failed to load the PayPal JS SDK script', error);
    isLoading.value = false;
  }
  loaded.value = true;
});

onUnmounted(() => {
  if (actionsWatcher.value !== null) {
    actionsWatcher.value();
  }
  periodicFetchActive.value = false;
});

function formatCurrency(total: number) {
  let neg = false;
  if (total < 0) {
    neg = true;
    total = Math.abs(total);
  }
  return (neg ? '-' : '') + total.toFixed(2);
}
</script>
