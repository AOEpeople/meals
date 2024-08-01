<template>
  <div class="items-center text-center xl:my-[42px] xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h2 class="m-0">
        {{ t('balance.transactions') }}
      </h2>
    </div>
    <div class="xl:justify-self-end">
      <Popover
        :translate-x-min="'-18%'"
        class="m-auto w-fit"
      >
        <template #button>
          <button
            class="hover:bg-highlight-2 btn-highlight-shadow mx-2 mb-6 mt-4 h-9 items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold text-white shadow-btn drop-shadow-btn transition-all duration-300 ease-out active:translate-y-0.5 active:shadow-btn-active"
          >
            <span class="align-middle leading-[10px]">+ {{ t('balance.add') }}</span>
          </button>
        </template>
        <template #panel="{ close }">
          <TransactionPanel @closePanel="close()" />
        </template>
      </Popover>
    </div>
  </div>
</template>

<script setup lang="ts">
import Popover from '@/components/misc/Popover.vue';
import TransactionPanel from '@/components/balance/TransactionPanel.vue';
import { userDataStore } from '@/stores/userDataStore';
import useSessionStorage from '@/services/useSessionStorage';
import { useI18n } from 'vue-i18n';
import { onMounted } from 'vue';
import postPaypalOrder from '@/api/postPaypalOrder';
import { transactionStore } from '@/stores/transactionStore';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

const { t } = useI18n();

onMounted(async () => {
  await checkForFailedOrder();
});

async function checkForFailedOrder() {
  const prevOrder = useSessionStorage().getAndClearData();
  if (prevOrder !== null) {
    const data = JSON.parse(prevOrder);
    const response = await postPaypalOrder(data.amount, data.orderId);
    if (response.ok) {
      userDataStore.adjustBalance(data.amount);
      transactionStore.fillStore();
    } else {
      useFlashMessage().sendFlashMessage({
        type: FlashMessageType.ERROR,
        message: t('balance.paypal_error')
      });
    }
  }
}
</script>
