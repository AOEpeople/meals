<template>
  <div
    class="grid grid-cols-1 divide-y"
  >
    <div
      :class="[0 ? 'border-gray-300' : 'border-gray-200', 'border-b', 'flex', 'items-center', 'gap-4', 'pb-2.5', 'h-12']"
    >
      <DialogTitle
        class="inline-block h-6 flex-none align-middle text-[11px] font-bold uppercase tracking-[1.5px] text-primary"
      >
        {{ t('dashboard.print') }}
      </DialogTitle>
      <FilterInput
        v-model="filterInput"
        :label-text="t('menu.search')"
        :label-visible="false"
        class="col-span-3 row-start-2 mr-8 justify-self-center min-[400px]:grow sm:col-span-1 sm:col-start-1 sm:justify-self-start"
      />
    </div>

    <table
      :class="'w-full overflow-auto'"
    >
      <tbody>
        <template
          v-for="(participant, index) in filteredParticipants"
          :key="index"
        >
          <tr
            :class="[0 ? 'border-gray-300' : 'border-gray-200', 'border-b']"
          >
            <td
              class="leading- h-6 whitespace-nowrap py-1 text-[12px] font-light text-primary"
            >
              {{ participant }}
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { filterParticipantsList } from '@/services/filterParticipantsList';
import { DialogTitle } from '@headlessui/vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import FilterInput from '../misc/FilterInput.vue';

const progress = useProgress().start()

const props = defineProps<{
  date: string
}>();

const { filteredParticipants, setFilter } = filterParticipantsList(props.date);
const { t } = useI18n();

const filterInput = ref('');

watch(
  () => filterInput.value,
  () => setFilter(filterInput.value)
);

progress.finish()
</script>