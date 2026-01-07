<template>
  <div>
    <InformationCircleIcon
      class="size-[24px] text-primary hover:cursor-pointer"
      @click="showParticipations = true"
    />
    <PopupModal :isOpen="showParticipations">
      <!-- maincontainer with padding -->
      <div class="max-h-96 min-w-[300px] overflow-y-auto p-[16px] pt-[10px] sm:min-w-[576px]">
        <!-- inner container with content in full width -->
        <div class="w-full">
          <!-- dialog and close icon -->
          <div class="flex w-full items-center justify-between gap-4">
            <!-- DialogTitle -->
            <DialogTitle
              class="w-full whitespace-nowrap text-[10px] font-bold uppercase tracking-[1.5px] text-primary sm:inline-block sm:h-6 sm:align-middle sm:text-[12px]"
            >
              {{ t('dashboard.participations').replace('%EventTitle%', eventTitle) }}
            </DialogTitle>
            <!-- IconCancel -->
            <IconCancel
              :btn-text="t('combiModal.close')"
              class="cursor-pointer"
              @click="showParticipations = false"
            />
          </div>
          <!-- loading spinner -->
          <RefreshIcon
            v-if="isLoading"
            class="aspect-[1/1] h-[75px] animate-spin-loading place-self-center p-4 text-primary drop-shadow-[0_0_0.35rem_rgba(0,0,0,0.75)]"
          />
          <!-- table with participants -->
          <table
            v-else-if="participations.length > 0"
            class="max-h-[300px] w-full overflow-y-auto"
          >
            <tbody>
              <template
                v-for="(participation, index) in participations"
                :key="`${String(participation)}_${index}`"
              >
                <tr :class="[index === 0 ? 'border-t border-gray-300' : 'border-b border-gray-200', 'border-b']">
                  <td
                    class="h-6 w-full whitespace-nowrap py-1 text-[12px] font-light text-primary"
                    data-cy="event-participant"
                  >
                    {{ String(participation) }}
                  </td>
                </tr>
              </template>
            </tbody>
          </table>

          <!-- message in case there are no participants -->
          <span
            v-else
            class="p-4"
          >
            {{ t('dashboard.noParticipants') }}
          </span>
        </div>
      </div>
    </PopupModal>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { DialogTitle } from '@headlessui/vue';
import IconCancel from '../misc/IconCancel.vue';
import PopupModal from '../misc/PopupModal.vue';
import { InformationCircleIcon, RefreshIcon } from '@heroicons/vue/outline';
import { useI18n } from 'vue-i18n';
import { useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { getParticipantsForEvent } = useEvents();

const props = withDefaults(
  defineProps<{
    eventTitle?: string;
    date: string;
    participationId: number;
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
    participations.value = ((await getParticipantsForEvent(props.date, props.participationId)) as string[]).sort();
    isLoading.value = false;
  }
});
</script>
