<template>
  <div class="grid max-h-96">
    <div
      class="grid max-h-24 grid-cols-1 grid-rows-2 gap-0.5 sm:grid-cols-2 sm:grid-rows-1"
      :class="[0 ? 'border-gray-300' : 'border-gray-200', 'border-b', 'sm:items-center', 'sm:gap-4', 'sm:pb-2.5']"
    >
      <DialogTitle
        class="cols-start-1 margin-0 sm:padding-right-2 padding-right-1 sm:margin=1.5 whitespace-nowrap text-[10px] font-bold uppercase tracking-[1.5px] text-primary sm:inline-block sm:h-6 sm:align-middle sm:text-[12px]"
      >
      {{ t('printList.title') }} {{ dateString }}
      </DialogTitle>
      <InputLabel
        v-model="filterInput"
        :label-text="t('menu.search')"
        :label-visible="false"
        overwrite-container-styles="flex-row item-center"
        overwrite-input-style="focus-visible:ring-offset-orange-300 relative flex h-8 items-center rounded-3xl border border-[#B4C1CE] bg-white mr-0 pl-4 pr-4 text-left text-[12px] leading-5 text-[#9CA3AF] focus:outline-none focus-visible:border-[#FF890E] focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 min-[380px]: sm:w-32 sm:mr-6"
      />
    </div>

    <table :class="'w-full scroll-m-0.5 overflow-y-scroll'">
      <tbody>
        <template
          v-for="(participant, index) in filteredParticipants"
          :key="index"
        >
          <tr :class="[0 ? 'border-gray-300' : 'border-gray-200', 'border-b']">
            <td class="leading- h-6 whitespace-nowrap py-1 text-[12px] font-light text-primary">
              <div v-if="participant === 'noParticipants'">
                {{ t('flashMessage.success.participations.no') }}
              </div>
              <div v-else>
                {{ participant }}
              </div>
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
import InputLabel from '../misc/InputLabel.vue';

const progress = useProgress().start();

const props = defineProps<{
  date: string;
  dateString: string,
  weekday: string
}>();

const { filteredParticipants, setFilter } = filterParticipantsList(props.date);
const { t } = useI18n();

const filterInput = ref('');

watch(
  () => filterInput.value,
  () => setFilter(filterInput.value)
);

progress.finish();
</script>
