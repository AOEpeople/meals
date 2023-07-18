<template>
  <div>
    <h2>{{ t('menu.participations') }}</h2>
    <h4>{{ `For Week with ID: ${week}` }}</h4>
    <div
      class="mb-8 mt-4 max-w-screen-aoe overflow-x-auto"
    >
      <MenuTable
        :week-id="parseInt(week)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import MenuTable from '@/components/menuParticipants/MenuTable.vue';
import { useI18n } from 'vue-i18n';

defineProps<{
  week: string;
}>();

const { t } = useI18n();

</script>