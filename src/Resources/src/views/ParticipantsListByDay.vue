<template>
  <table >
    <tbody >
      <template v-for="(participant, slotName) in listData.data" :key="String(slotName)">
        <tr v-for="(participations, participantName, index) in participant" :key="index"
          :class="[index === 0 ? 'border-gray-300' : 'border-gray-200', 'border-t']">
          <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-primary sm:pl-6">
            {{ String(participantName) }}
          </td>
        </tr>
      </template>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { useParticipantsByDayData } from '@/api/getParticipationsByDate';
import { useProgress } from '@marcoschulte/vue3-progress';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const progress = useProgress().start()
const { t, locale } = useI18n()

const { listData } = useParticipantsByDayData();

const mealNames = computed(() => {
  const names: string[] = [];
  Object.values(listData.meals).forEach(meal => {
    names.push(locale.value === 'en' ? meal.title.en : meal.title.de);
  });
  return names;
});

const participationCount = computed(() => {
  const count: number[] = [];
  Object.values(listData.meals).forEach(meal => {
    count.push(meal.participations ? meal.participations : 0);
  })
  return count;
})

const dateString = computed(() => new Date(Date.parse(listData.day.date)).toLocaleDateString(locale.value, { weekday: 'long', month: 'numeric', day: 'numeric' }));

progress.finish()
</script>