<template>
  <form
    class="w-[300px] p-4 sm:w-[400px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="w-full text-center">
      {{ t('costs.payment').replace('#name#', getFullNameByUser(username)) }}
    </h3>
    <InputLabel
      v-model="amount"
      :type="'number'"
      :min="1"
      :required="true"
    />
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useCosts } from '@/stores/costsStore';
import InputLabel from '../misc/InputLabel.vue';
import { ref } from 'vue';
import SubmitButton from '../misc/SubmitButton.vue';

const { t } = useI18n();
const { sendCashPayment, getFullNameByUser } = useCosts();

const amount = ref('1');

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
