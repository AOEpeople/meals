<template>
  <Listbox
    v-slot="{ open }"
    v-model="selectedSlot"
    :disabled="disabled"
  >
    <div class="relative w-full">
      <ListboxButton
        :class="[open ? 'rounded-t-2xl border-x border-t' : 'rounded-3xl border', disabled ? '' : 'cursor-pointer']"
        class="focus-visible:ring-offset-orange-300 relative flex h-8 w-full items-center border-[#B4C1CE] bg-white pl-4 pr-2 text-left focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 min-[380px]:pr-10 sm:w-64"
      >
        <span class="text-gray block truncate text-[12px] leading-5 min-[380px]:text-note">
          {{ selectedSlot.slug === 'auto' ? t('dashboard.slot.auto') : selectedSlot.title }}
          <span v-if="selectedSlot.limit !== 0">
            {{ '( ' + selectedSlot.count + ' / ' + selectedSlot.limit + ' )' }}
          </span>
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronDownIcon
            class="size-5 text-gray-400"
            :class="open ? 'rotate-180 transform' : ''"
            aria-hidden="true"
          />
        </span>
      </ListboxButton>
      <ListboxOptions
        class="absolute -mt-px max-h-60 w-full overflow-auto rounded-b-2xl border-x border-b border-[#B4C1CE] bg-white text-[12px] leading-5 shadow-lg focus:outline-none min-[380px]:text-note sm:text-sm"
      >
        <template v-for="slot in day.slots">
          <ListboxOption
            v-if="slot.id !== 0 || !isParticipating"
            v-slot="{ active, selected }"
            :key="slot.slug as string"
            :value="slot"
            as="template"
          >
            <li
              :class="selected ? 'bg-[#F4F4F4]' : 'hover:bg-[#FAFAFA]'"
              class="cursor-pointer pl-4"
            >
              <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate py-2 text-note']">
                {{ slot.slug === 'auto' ? t('dashboard.slot.auto') : slot.title }}
                <span v-if="slot.limit !== 0">
                  {{ '( ' + slot.count + ' / ' + slot.limit + ' )' }}
                </span>
              </span>
            </li>
          </ListboxOption>
        </template>
      </ListboxOptions>
    </div>
  </Listbox>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';

import { useI18n } from 'vue-i18n';
import { useUpdateSelectedSlot } from '@/api/putUpdateSelectedSlot';
import { ChevronDownIcon } from '@heroicons/vue/solid';
import useEventsBus from '@/tools/eventBus';
import { type Day } from '@/api/getDashboardData';

const props = defineProps<{
  dayID: number | string | undefined;
  day: Day;
}>();

const { t } = useI18n();

const selectedSlot = ref(props.day.slots[props.day.activeSlot] ?? props.day.slots['0']);
const activeSlot = computed(() => props.day.slots[props.day.activeSlot]);
const disabled = computed(() => props.day.isLocked);

let isParticipating = ref(false);

watch(activeSlot, () => {
  selectedSlot.value = activeSlot.value;
});

// if dayID is set the component comes from the dashboard
if (props.dayID) {
  isParticipating.value = checkIfParticipating();

  watch(
    props.day.meals,
    () => {
      isParticipating.value = checkIfParticipating();
    },
    { deep: true }
  );

  watch(activeSlot, () => {
    selectedSlot.value = activeSlot.value;
  });

  watch(selectedSlot, () => {
    props.day.activeSlot = selectedSlot.value.id;
    if (isParticipating.value) {
      let data = {
        slotID: selectedSlot.value.id,
        dayID: props.dayID
      };
      useUpdateSelectedSlot(JSON.stringify(data));
    }
  });

  function checkIfParticipating() {
    for (const mealId in props.day.meals) {
      if (props.day.meals[mealId].variations !== null) {
        for (const variationsId in props.day.meals[mealId].variations) {
          if (props.day.meals[mealId].variations[variationsId].isParticipating) {
            return true;
          }
        }
      }
      if (props.day.meals[mealId].isParticipating) {
        return true;
      }
    }

    return false;
  }
} else {
  const { emit } = useEventsBus();
  watch(selectedSlot, () => emit('guestChosenSlot', selectedSlot.value.id));
}
</script>
