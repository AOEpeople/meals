<template>
  <table>
    <tbody>
      <template
        v-for="(participant, slotName) in listData.data"
        :key="String(slotName)"
      >
        <tr
          v-for="(participations, participantName, index) in participant"
          :key="index"
          :class="[index === 0 ? 'border-gray-300' : 'border-gray-200', 'border-b']"
        >
          <td
            class="text-s leading- w-2/5 whitespace-nowrap py-4 pl-4 pr-3 font-light"
          >
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

const progress = useProgress().start()

const props = defineProps<{
  date: string,
}>();
console.log(props.date)
const { listData } = useParticipantsByDayData(props.date);

progress.finish()
</script>