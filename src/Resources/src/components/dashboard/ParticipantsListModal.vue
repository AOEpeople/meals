<template>
  <Dialog
    :open="openParticipantsModal"
    class="relative z-50"
    @close="closeParticipantsModal()"
  >
    <div class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.1)] p-4">
      <DialogPanel
        class="relative inset-0 mx-auto h-auto w-max overflow-auto rounded-lg border-r-8 border-white bg-white px-4 pb-4 pt-2.5 text-left shadow-xl"
      >
        <IconCancel
          :btn-text="t('combiModal.close')"
          class="absolute right-4 top-6 z-10 flex-1 cursor-pointer"
          @click="closeParticipantsModal()"
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

function closeParticipantsModal() {
    emit('closeDialog');
}
</script>
