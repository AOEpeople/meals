<template>
  <table
    class="flex w-full table-fixed border-collapse border-spacing-0 flex-col border-0 border-none"
    :style="{ height: tableHeight }"
  >
    <ParticipantsTableHead
      id="tableHead"
      ref="tableHead"
    />
    <ParticipantsTableBody />
  </table>
</template>

<script setup lang="ts">
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import ParticipantsTableBody from './ParticipantsTableBody.vue';
import ParticipantsTableHead from './ParticipantsTableHead.vue';
import { useComponentHeights } from '@/services/useComponentHeights';

const { maxTableHeight, setTableHeadHight, windowWidth } = useComponentHeights();

const tableHead = ref<InstanceType<typeof ParticipantsTableHead> | null>(null);

const tableHeight = computed(() => {
  return `${maxTableHeight.value}px`;
});

watch(windowWidth, () => {
  if (tableHead.value) {
    setTableHeadHight(0, 'tableHead');
  }
});

onMounted(() => {
  if (tableHead.value) {
    setTableHeadHight(0, 'tableHead');
  }
});

onUpdated(() => {
  if (tableHead.value) {
    setTableHeadHight(0, 'tableHead');
  }
});

if (process?.env?.NODE_ENV === 'TEST') {
  defineExpose({ tableHeight });
}
</script>
