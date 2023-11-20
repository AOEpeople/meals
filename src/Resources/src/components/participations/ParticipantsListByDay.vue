<template>
  <table>
    <tbody>
      <template
        v-for="(participant, slotName, index) in filteredParticipants"
        :key="String(slotName)"
      >
        <tr
          :class="[index === 0 ? 'border-gray-300' : 'border-gray-200', 'border-b']"
        >
          <td
            class="text-s leading- w-2/5 whitespace-nowrap py-4 pl-4 pr-3 font-light"
          >
            {{ String(participant) }}
          </td>
        </tr>
      </template>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { filterParticipantsList } from './filterParticipantsList';

const progress = useProgress().start()

const props = defineProps<{
  date: string
}>();
const { filteredParticipants } = filterParticipantsList(props.date);
//let filteredParticipants  = filterParticipantsList(props.filterString, props.date);

progress.finish()
</script>@/components/participations/filterParticipantsList