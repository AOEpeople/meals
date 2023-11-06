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
        class="relative inset-0 w-96 overflow-hidden rounded-lg bg-white p-4 text-left drop-shadow-2xl sm:my-8 sm:p-6"
      >
        <DialogTitle
          class="text-sm"
        >
          {{ t('dashboard.print') }}
        </DialogTitle>
        <p>Filter: {{ filterString }}</p>
        <input
          v-model="filterString"
          :placeholder="t('costs.search')"
          class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
        >
        <ParticipantsListByDay
          :date="date"
          :filterString="filterString"
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
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CancelButton from '../misc/CancelButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import ParticipantsListByDay from '../participations/ParticipantsListByDay.vue';
import { ref } from 'vue';
const { t } = useI18n();

const props = defineProps<{
  modelValue: string,
  openParticipantsModal: boolean,
  date: string,
}>();

const emit = defineEmits(['closeDialog','update:modelValue']);


function closeParticipantsModal(doSubmit: boolean) {
if (doSubmit === false){
  emit('closeDialog');
}
}

const filter = computed({
  get() {
    return props.modelValue;
  },
  set(filter) {
    emit('update:modelValue', filter);
    console.log(filter);
  }
})
</script>