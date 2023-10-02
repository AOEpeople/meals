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

const props = defineProps<{
  date: string,
}>();
console.log(props.date)
const { listData } = useParticipantsByDayData(props.date);


// const participationCount = computed(() => {
//   const count: number[] = [];
//   Object.values(listData.meals).forEach(meal => {
//     count.push(meal.participations ? meal.participations : 0);
//   })
//   return count;
// })

progress.finish()
</script>