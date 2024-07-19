<template>
  <tbody class="z-10">
    <MenuTableDataRows
      v-for="participant in filteredParticipants"
      :key="participant"
      :week-id="weekId"
      :participant="participant"
    />
    <MenuTableRow :week-id="weekId">
      <template #firstCell>
        <td class="sticky left-0 border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] p-2 text-start">
          <span>{{ t('menu.total') }}</span>
        </td>
      </template>
      <template #dayMeals="{ dayId, meals }">
        <td
          v-for="(meal, mealIndex) in meals"
          :key="`${String(meal.id)}.${String(mealIndex)}`"
          class="border-b-2 border-r-2 border-solid border-gray-200 p-2 text-center"
        >
          <span>{{ countBookedMeal(dayId, mealIdToDishIdDict.get(meal.id)) }}</span>
        </td>
      </template>
    </MenuTableRow>
  </tbody>
</template>

<script setup lang="ts">
import { useParticipations } from '@/stores/participationsStore';
import { useMealIdToDishId } from '@/services/useMealIdToDishId';
import { useI18n } from 'vue-i18n';
import MenuTableRow from './MenuTableRow.vue';
import MenuTableDataRows from '@/components/menuParticipants/MenuTableDataRows.vue';
import { computed } from 'vue';

const props = defineProps<{
  weekId: number;
}>();

const { countBookedMeal, getParticipants, getFilter } = useParticipations(props.weekId);
const { mealIdToDishIdDict } = useMealIdToDishId(props.weekId);
const { t } = useI18n();
const participants = computed(() => getParticipants());

const filteredParticipants = computed(() => {
  if (getFilter() === '') return participants.value;
  return participants.value.filter((participant) => participant.toLowerCase().includes(getFilter().toLowerCase()));
});
</script>
