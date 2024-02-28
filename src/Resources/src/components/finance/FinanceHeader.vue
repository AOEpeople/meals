<template>
  <div
    v-if="showControls === true"
    class="mb-8 flex items-center gap-5 align-middle"
  >
    <VueDatePicker
      v-model="date"
      range
      :enable-time-picker="false"
      :clearable="false"
      :format="locale === 'de' ? 'dd.MM.yyyy' : 'MM/dd/yyyy'"
      auto-apply
    />
  </div>
  <div class="grid grid-rows-1 items-center xl:my-[24px] xl:grid-cols-2">
    <h2 class="m-0 text-center max-[420px]:text-[24px] xl:justify-self-start">
      {{ dateRange }}
    </h2>
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
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DownloadIcon } from '@heroicons/vue/outline';
import VueDatePicker from '@vuepic/vue-datepicker';
import moment from 'moment/moment';

const { t, locale } = useI18n();

const emit = defineEmits(['dateChanged', 'generatePdf']);

defineProps<{
  dateRange: string;
  showControls: boolean;
}>();

const minDate = ref(moment().subtract(1, 'months').startOf('month').format('MM-DD-YYYY'));
const maxDate = ref(moment().subtract(1, 'months').endOf('month').format('MM-DD-YYYY'));

const date = computed({
  get() {
    return [minDate.value, maxDate.value];
  },
  set(value) {
    minDate.value = moment(value[0]).format('MM-DD-YYYY');
    maxDate.value = moment(value[1]).format('MM-DD-YYYY');

    emit('dateChanged', value);
  }
});
</script>
