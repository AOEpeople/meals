<template>
  <div>
    <InformationCircleIcon
      class="size-[24px] text-primary hover:cursor-pointer"
      @click="showParticipations = true"
    />
    <PopupModal :isOpen="showParticipations">
      <div
        class="grid max-h-[70vh] min-w-[300px] max-w-[310px] grid-cols-1 grid-rows-[auto_minmax(0,1fr)_auto] md:max-w-lg"
      >
        <div class="flex h-[48px] flex-row gap-2 rounded-t-lg bg-primary-2 p-2">
          <span
            class="grow self-center justify-self-center truncate font-bold uppercase leading-4 tracking-[3px] text-white"
          >
            {{ t('dashboard.participations').replace('%EventTitle%', eventTitle) }}
          </span>
          <XCircleIcon
            class="size-8 cursor-pointer self-end text-white transition-transform hover:scale-[120%] hover:text-[#FAFAFA]"
            @click="showParticipations = false"
          />
        </div>
        <RefreshIcon
          v-if="isLoading"
          class="aspect-[1/1] h-[75px] animate-spin-loading place-self-center p-4 text-primary drop-shadow-[0_0_0.35rem_rgba(0,0,0,0.75)]"
        />
        <ul
          v-else-if="participations.length > 0"
          class="overflow-y-auto p-4"
        >
          <li
            v-for="(participation, index) in participations"
            :key="`${String(participation)}_${index}`"
            class="border-b-2 p-2 last:border-b-0"
          >
            {{ String(participation) }}
          </li>
        </ul>
        <span
          v-else
          class="p-4"
        >
          {{ t('dashboard.noParticipants') }}
        </span>
        <span class="w-full border-t-2 p-2 text-center font-bold">
          {{ t('dashboard.participationCount').replace('%count%', participations.length.toString()) }}
        </span>
      </div>
    </PopupModal>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import PopupModal from '../misc/PopupModal.vue';
import { InformationCircleIcon, RefreshIcon } from '@heroicons/vue/outline';
import { XCircleIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';
import { useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { getParticipantsForEvent } = useEvents();

const props = withDefaults(
  defineProps<{
    eventTitle?: string;
    date: string;
  }>(),
  {
    eventTitle: ''
  }
);

const showParticipations = ref(false);
const participations = ref<string[]>([]);
const isLoading = ref(false);

watch(showParticipations, async () => {
  if (showParticipations.value === true) {
    isLoading.value = true;
    participations.value = (await getParticipantsForEvent(props.date)) as string[];
    isLoading.value = false;
  }
});
</script>
