<template>
  <Modal
    :show="show"
    @close="$emit('close')"
  >
    <div class="w-[300px] rounded-lg bg-white p-6 shadow-lg md:w-[400px]">
      <h3 class="mb-4 text-lg font-semibold">{{ t('confirmOfferMeal.popover.title') }}</h3>
      <button
        type="button"
        class="btn-primary mb-1 inline-flex w-full justify-center"
        @click="$emit('confirm')"
      >
        {{ t('confirmOfferMeal.popover.yesButton') }}
      </button>
      <button
        type="button"
        class="btn-secondary inline-flex w-full justify-center"
        @click="$emit('close')"
      >
        {{ t('confirmOfferMeal.popover.noButton') }}
      </button>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Modal from '@/components/misc/Modal.vue';
const { t } = useI18n();

defineProps<{
  show: boolean;
}>();

defineEmits<{
  close: [];
  confirm: [];
}>();
</script>
