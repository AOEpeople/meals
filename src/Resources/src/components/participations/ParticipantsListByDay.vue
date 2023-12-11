<template>
  <InputLabel
    v-model="filterInput"
    :label-text="t('menu.search')"
    :label-visible="false"
    class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
  />
  <table>
    <tbody>
      <template
        v-for="(participant, index) in filteredParticipants"
        :key="index"
      >
        <tr
          :class="[0 ? 'border-gray-300' : 'border-gray-200', 'border-b']"
        >
          <td
            class="text-s leading- w-2/5 whitespace-nowrap py-4 pl-4 pr-3 font-light"
          >
            {{ participant }}
          </td>
        </tr>
      </template>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputLabel from '../misc/InputLabel.vue';
import { filterParticipantsList } from './filterParticipantsList';

const progress = useProgress().start()

const props = defineProps<{
  date: string
}>();

const { filteredParticipants, setFilter } = filterParticipantsList(props.date);
const { t } = useI18n();
//let filteredParticipants  = filterParticipantsList(props.filterString, props.date);

const filterInput = ref('');

watch(
  () => filterInput.value,
  () => setFilter(filterInput.value)
);

progress.finish()
</script>../../services/filterParticipantsList