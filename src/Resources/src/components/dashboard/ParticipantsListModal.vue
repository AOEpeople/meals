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
        class="relative overflow-hidden rounded-lg bg-white p-4 text-left drop-shadow-2xl  sm:my-8 sm:p-6 "
      >
        <DialogTitle>
          {{ t('dashboard.print') }}
        </DialogTitle>
        <ParticipantsListByDay
          :date="date"
        />
        <div class="flex max-h-96 flex-row">
          <CancelButton
            :btn-text="t('combiModal.cancel')"
            class="flex-1 cursor-pointer "
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
import { useProgress } from '@marcoschulte/vue3-progress';

import ParticipantsListByDay from '@/views/ParticipantsListByDay.vue';

import { useComponentHeights } from '@/services/useComponentHeights';
import { onMounted, onUnmounted } from 'vue';

const { loadShowParticipations, activatePeriodicFetch, disablePeriodicFetch } = getShowParticipations();
const { addWindowHeightListener, removeWindowHeightListener } = useComponentHeights();
const progress = useProgress().start();


const { t } = useI18n();

const props = defineProps<{
  openParticipantsModal: boolean,
  date: string,
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

onMounted(async () => {
  await loadShowParticipations();
  progress.finish();
  activatePeriodicFetch();
  addWindowHeightListener();
});

onUnmounted(() => {
  disablePeriodicFetch();
  removeWindowHeightListener();
});

</script>