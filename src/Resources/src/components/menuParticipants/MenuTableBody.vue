<template>
  <tbody>
    <MenuTableDataRows :week-id="weekId" />
    <MenuTableRow :week-id="weekId">
      <template #firstCell>
        <td class="border-y-2 border-solid border-gray-200 p-2 text-center">
          <span>{{ t('menu.total') }}</span>
        </td>
      </template>
      <template #dayMeals="{ dayId, meals }">
        <td
          v-for="meal, mealIndex in meals"
          :key="`${meal.id}.${mealIndex}`"
          class="border-2 border-solid border-gray-200 p-2 text-center"
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

const props = defineProps<{
  weekId: number
}>();

const { countBookedMeal } = useParticipations(props.weekId);
const { mealIdToDishIdDict } = useMealIdToDishId(props.weekId);
const { t } = useI18n();

</script>