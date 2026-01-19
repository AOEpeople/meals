<template>
  <form
    class="flex w-[300px] flex-col p-4 sm:w-[400px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="w-full text-center">
      {{ t('costs.payment').replace('#name#', getFullNameByUser(username)) }}
    </h3>
    <MoneyInput v-model="amount" />
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useCosts } from '@/stores/costsStore';
import { ref } from 'vue';
import SubmitButton from '../misc/SubmitButton.vue';
import MoneyInput from '@/components/misc/MoneyInput.vue';

const { t } = useI18n();
const { sendCashPayment, getFullNameByUser } = useCosts();

const amount = ref(0);

const props = defineProps<{
  username: string;
}>();

const emit = defineEmits(['closePanel']);

async function onSubmit() {
  const parsedAmount = parseFloat(amount.value);
  if (parsedAmount > 0) {
    await sendCashPayment(props.username, parsedAmount);
  }
  emit('closePanel');
}
</script>
