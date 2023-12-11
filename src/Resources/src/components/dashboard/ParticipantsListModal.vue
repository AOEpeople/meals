<template>
  <Dialog
    :open="openParticipantsModal"
    class="relative z-50"
    @close="closeParticipantsModal(false)"
  >
    <div
      class="fixed inset-0 flex items-center justify-center bg-black/30 p-4"
    >
      <DialogPanel
        class="relative inset-0 max-h-96 w-96 overflow-scroll rounded-lg bg-white p-4 text-left drop-shadow-2xl sm:my-8 sm:p-6"
      >
        <DialogTitle
          class="text-sm"
        >
          {{ t('dashboard.print') }}
        </DialogTitle>
        <ParticipantsListByDay
          :date="date"
        />
        <div class="flex max-h-96 flex-row pt-4">
          <CancelButton
            :btn-text="t('combiModal.close')"
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
import { useI18n } from 'vue-i18n';
import CancelButton from '../misc/CancelButton.vue';
import ParticipantsListByDay from '../participations/ParticipantsListByDay.vue';
import { filterParticipantsList } from '@/services/filterParticipantsList';


const { t } = useI18n();

const props = defineProps<{
  openParticipantsModal: boolean,
  date: string,
}>();

const emit = defineEmits(['closeDialog','update:modelValue','update:filterValue']);
// const filterString = ref("");
const { setFilter } = filterParticipantsList(props.date);


function closeParticipantsModal(doSubmit: boolean) {
if (doSubmit === false){
  emit('closeDialog');
}
}
</script>../../services/filterParticipantsList