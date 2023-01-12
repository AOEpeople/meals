<template>
  <Listbox
    v-model="selectedSlot"
    :disabled="disabled"
  >
    <div class="relative">
      <ListboxButton class="flex items-center rounded-3xl border h-8 focus-visible:ring-offset-orange-300 relative w-64 cursor-default bg-white pr-10 pl-3 text-left focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2">
        <span class="block truncate text-note text-gray">
          {{ selectedSlot.slug === 'auto' ? t('dashboard.slot.auto') : selectedSlot.title }}
          <span v-if="selectedSlot.limit !== 0">
            {{ '( ' + selectedSlot.count + ' / ' + selectedSlot.limit + ' )' }}
          </span>
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronDownIcon
            class="h-5 w-5 text-gray-400"
            aria-hidden="true"
          />
        </span>
      </ListboxButton>

      <transition
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <ListboxOptions class="absolute max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-note shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm">
          <template v-for="slot in day.slots">
            <ListboxOption
              v-if="slot.id !== 0 || !isParticipating"
              v-slot="{ active, selected }"
              :key="slot.slug"
              :value="slot"
              as="template"
            >
              <li
                :class="[
                  active ? 'bg-amber-100 text-amber-900 cursor-pointer' : 'text-gray-900',
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                ]"
              >
                <span :class="[selected ? 'font-medium' : 'font-normal', 'block truncate text-note']">
                  {{ slot.slug === 'auto' ? t('dashboard.slot.auto') : slot.title }}
                  <span v-if="slot.limit !== 0">
                    {{ '( ' + slot.count + ' / ' + slot.limit + ' )' }}
                  </span>
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-1 text-amber-600"
                >
                  <CheckIcon
                    class="h-5 w-5"
                    aria-hidden="true"
                  />
                </span>
              </li>
            </ListboxOption>
          </template>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>

<script setup>
import {computed, ref, watch} from 'vue'
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue'

import {useI18n} from "vue-i18n";
import {useUpdateSelectedSlot} from "@/api/postUpdateSelectedSlot";
import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/solid'
import {dashboardStore} from "@/stores/dashboardStore";

const props = defineProps([
  'weekID',
  'dayID',
    'day'
])
const { t } = useI18n()

const day = props.day ? props.day : dashboardStore.getDay(props.weekID, props.dayID)

const meals = day.meals
const selectedSlot = ref(day.slots[day.activeSlot])
const activeSlot = computed(() => day.slots[day.activeSlot])
const disabled = computed(() => day.isLocked)

let isParticipating = ref(false)

if (!props.day) {
  isParticipating = ref(checkIfParticipating())
  watch(meals, () => {
    isParticipating.value = checkIfParticipating()
  }, { deep: true })
  watch(activeSlot, () => {
    selectedSlot.value = activeSlot.value
  })

  watch(selectedSlot, () => {
    day.activeSlot = selectedSlot.value.id
    if (isParticipating.value) {
      let data = {
        slotID: selectedSlot.value.id,
        dayID: props.dayID
      }
      useUpdateSelectedSlot(JSON.stringify(data))
    }
  })

  function checkIfParticipating() {
    for (const mealId in meals) {
      if (meals[mealId].variations !== null) {
        for (const variationsId in meals[mealId].variations) {
          if(meals[mealId].variations[variationsId].isParticipating) {
            return true
          }
        }
      }
      if(meals[mealId].isParticipating) {
        return true
      }
    }

    return false
  }
}
</script>
