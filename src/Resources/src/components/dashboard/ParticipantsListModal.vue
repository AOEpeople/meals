<!-- eslint-disable vue/html-closing-bracket-spacing -->
<template>
  <Dialog
    :open="openParticipantsModal"
    class="relative z-50"
    @close="closeParticipantsModal(false)"
  >
    <div
      class="fixed inset-0 flex items-center justify-center p-4"
    >
      <DialogPanel
        class="relative overflow-hidden rounded-lg bg-white p-4 text-left shadow-xl sm:my-8 sm:p-6"
      >
        <DialogTitle>
          {{ t('dashboard.print') }}
        </DialogTitle>
        <ParticipationsTable
          class="max-w-md overflow-auto"
        />
        <div class="flex max-h-96 flex-row">
          <CancelButton
            :btn-text="t('combiModal.cancel')"
            class="flex-1 cursor-pointer"
            @click="closeParticipantsModal(false)"
          />
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

import CancelButton from '../misc/CancelButton.vue';

import { getShowParticipations } from '@/api/getShowParticipations';
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';

const { participationsState } = getShowParticipations();
const { t } = useI18n();
const loaded = ref(false);
const props = defineProps<{
  openParticipantsModal: boolean,
  // mealId: number,
  // dayId: string,
  // weekId: number
}>();

const emit = defineEmits(['closeDialog']);

const selectedCombi = ref<number[]>([-1, -1]);

function closeParticipantsModal(doSubmit: boolean) {
  if (doSubmit === true && selectedCombi.value.includes(-1) === false) {
    emit('closeDialog', selectedCombi.value);
  } else {
    emit('closeDialog', []);
  }
}
</script>