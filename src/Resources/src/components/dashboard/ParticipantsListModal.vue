<template>
  <Dialog
    :open="openParticipantsModal"
    class="relative z-50"
    @close="closeParticipantsModal()"
  >
    <div class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.1)] p-4">
      <DialogPanel
        class="day-shadow relative inset-0 mx-auto h-auto w-96 overflow-auto rounded-lg bg-white px-4 pb-4 pt-2.5 text-left drop-shadow-2xl sm:w-max"
      >
        <IconCancel
          :btn-text="t('combiModal.close')"
          class="absolute right-4 top-5 z-10 flex-1 cursor-pointer"
          @click="closeParticipantsModal(false)"
        />
        <ParticipantsListByDay
          :date="date"
          :dateString="dateString"
          :weekday="weekday"
        />
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';
import IconCancel from '../misc/IconCancel.vue';
import ParticipantsListByDay from '../participations/ParticipantsListByDay.vue';

const { t } = useI18n();

defineProps<{
  openParticipantsModal: boolean;
  date: string;
  dateString: string;
  weekday: string;
}>();

const emit = defineEmits(['closeDialog', 'update:modelValue', 'update:filterValue']);

function closeParticipantsModal() {
  emit('closeDialog');
}
</script>
