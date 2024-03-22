<template>
  <Dialog
    :open="openParticipantsModal"
    class="relative z-50"
    @close="closeParticipantsModal(false)"
  >
    <div class="fixed inset-0 flex items-center justify-center bg-black/30 p-4">
      <DialogPanel
        class="day-shadow relative inset-0 mx-auto h-auto w-max overflow-auto rounded-lg bg-white px-4 pb-4 pt-2.5 text-left drop-shadow-2xl"
      >
        <IconCancel
          :btn-text="t('combiModal.close')"
          class="absolute right-4 top-6 z-10 flex-1 cursor-pointer"
          @click="closeParticipantsModal(false)"
        />
        <ParticipantsListByDay :date="date" />
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
}>();

const emit = defineEmits(['closeDialog', 'update:modelValue', 'update:filterValue']);

function closeParticipantsModal(doSubmit: boolean) {
  if (doSubmit === false) {
    emit('closeDialog');
  }
}
</script>
