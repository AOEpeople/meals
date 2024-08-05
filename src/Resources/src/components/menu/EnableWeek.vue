<template>
  <div class="flex flex-row items-center gap-2">
    <span class="text-[12px]">
      {{ t('menu.enableWeek') }}
    </span>
    <Switch
      :sr="t('menu.enableWeek')"
      :initial="week.enabled"
      @toggle="(value) => (week.enabled = value)"
    />
  </div>
</template>

<script setup lang="ts">
import { type WeekDTO } from '@/interfaces/DayDTO';
import Switch from '../misc/Switch.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  week: WeekDTO;
}>();
</script>
