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
        <p>Filter: {{ filterInput }}</p>
        <!-- <input
          v-model="filterString"
          :placeholder="t('costs.search')"
          class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
          @input="$emit('update:filterValue')"
        > -->
        <InputLabel
          v-model="filterInput"
          :label-text="t('dish.search')"
          :label-visible="false"
          class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
        />
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
import InputLabel from '@/components/misc/InputLabel.vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import CancelButton from '../misc/CancelButton.vue';
import ParticipantsListByDay from '../participations/ParticipantsListByDay.vue';
import { filterParticipantsList } from '../participations/filterParticipantsList';

const { t } = useI18n();

const props = defineProps<{
  modelValue: string,
  openParticipantsModal: boolean,
  date: string,
  filterValue: string
}>();

const emit = defineEmits(['closeDialog','update:modelValue','update:filterValue']);
// const filterString = ref("");
const { setFilter } = filterParticipantsList(props.date);
const filterInput = ref('');

watch(
  () => filterInput.value,
  () => setFilter(filterInput.value)
);


function closeParticipantsModal(doSubmit: boolean) {
if (doSubmit === false){
  emit('closeDialog');
}
}
</script>