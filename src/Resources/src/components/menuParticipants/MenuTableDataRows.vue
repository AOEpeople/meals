<template>
  <MenuTableRow
    ref="row"
    :week-id="weekId"
    :class="{ 'bg-tb-shadow shadow-tb': editRow }"
  >
    <template #firstCell>
      <td
        class="sticky left-0 z-10 cursor-pointer whitespace-nowrap border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] p-2 text-[12px] hover:bg-tb-shadow hover:font-bold hover:shadow-tb"
        :class="{ 'bg-tb-shadow shadow-tb': editRow }"
        @click="editRow = !editRow"
      >
        <div class="grid grid-cols-[1fr_30px]">
          <span
            class="pr-2 text-left"
            :class="{ 'font-bold': editRow }"
          >
            {{ participant }}
          </span>
          <PencilIcon
            v-if="editRow === true"
            class="m-auto block size-5 text-primary"
          />
        </div>
      </td>
    </template>
    <template #dayMeals="{ dayId, meals }">
      <MenuTableData
        v-for="(meal, mealIndex) in meals"
        :key="`${String(meal.id)}.${String(mealIndex)}`"
        :edit="editRow"
        :participant="participant"
        :day-id="dayId"
        :week-id="weekId"
        :meal="meal"
      />
    </template>
  </MenuTableRow>
</template>

<script setup lang="ts">
import MenuTableRow from './MenuTableRow.vue';
import { ref, watch } from 'vue';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import MenuTableData from './MenuTableData.vue';
import { PencilIcon } from '@heroicons/vue/outline';

const editRow = ref<boolean>(false);
const row = ref<HTMLElement | null>(null);

defineProps<{
  weekId: number;
  participant: string;
}>();

watch(editRow, () => editRow.value === true && useDetectClickOutside(row, () => (editRow.value = false)));
</script>
