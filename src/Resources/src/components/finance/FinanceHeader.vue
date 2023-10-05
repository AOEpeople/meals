<template>
  <div
    v-if="showControls === true"
    class="mb-4 flex items-center gap-5 align-middle"
  >
    <VueDatePicker
      v-model="date"
      range
      :enable-time-picker="false"
      :clearable="false"
      :format="locale === 'de' ? 'dd.MM.yyyy' : 'MM/dd/yyyy'"
      auto-apply
      @update:model-value="handleDateChange"
    />
  </div>
  <div class="grid grid-rows-1 items-center xl:my-[24px] xl:grid-cols-2">
    <h1 class="m-0 text-center xl:justify-self-start">
      {{ dateRange }}
    </h1>
    <a
      v-if="showControls === true"
      class="cursor-pointer justify-self-end text-primary hover:text-secondary"
      @click="emit('generatePdf')"
    >
      <DownloadIcon
        class="inline-block w-6"
        aria-hidden="true"
      />
      <span class="pl-2 text-[14px] font-bold uppercase leading-[22px]">
        {{ t('finance.table.export') }}
      </span>
    </a>
  </div>
</template>

<script setup lang="ts">
import {onBeforeMount, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import { DownloadIcon } from '@heroicons/vue/outline'
import VueDatePicker from '@vuepic/vue-datepicker';
import moment from 'moment/moment';

const emit = defineEmits(['dateChanged', 'generatePdf'])
const { t, locale } = useI18n()
const date = ref()
let minDate = '';
let maxDate = '';

defineProps<{
  dateRange: string,
  showControls: boolean;
}>()

onBeforeMount(() => {
  minDate = getFirstDayPreviousMonth();
  maxDate = getLastDayPreviousMonth();

  date.value = [minDate, maxDate]
})

const handleDateChange = (modelData: Date[]) => {
  minDate = moment(modelData[0]).format('MM-DD-YYYY');
  maxDate = moment(modelData[1]).format('MM-DD-YYYY');

  emit('dateChanged', modelData);
}

const getFirstDayPreviousMonth = () => {
  return moment()
  .subtract(1, 'months')
  .startOf('month')
  .format('MM-DD-YYYY')
}
const getLastDayPreviousMonth = () => {
  return moment()
  .subtract(1, 'months')
  .endOf('month')
  .format('MM-DD-YYYY')
}
</script>