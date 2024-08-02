<template>
  <Combobox
    v-model="selectedEvent"
    as="span"
    class="relative w-full"
    nullable
  >
    <div
      ref="combobox"
      class="relative w-full"
    >
      <div
        class="flex w-full flex-row items-center overflow-hidden border-[#CAD6E1] bg-white text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
        :class="openProp ? 'rounded-t-[23px] border-x-2 border-b-[1px] border-t-2' : 'rounded-full border-2'"
        @click="handleClick"
      >
        <EventIcon
          class="ml-4 aspect-square h-full"
          :fill-colour="'fill-[#9CA3AF]'"
        />
        <ComboboxInput
          :displayValue="
          // @ts-ignore
            (event) => titleStringRepr
          "
          class="w-full truncate border-none px-4 py-2 text-[#9CA3AF] focus:outline-none"
          @change="setFilter($event.target.value)"
        />
        <XIcon
          class="mr-4 h-full w-10 cursor-pointer justify-self-end px-1 py-2 text-[#9CA3AF] transition-transform hover:scale-[120%]"
          aria-hidden="true"
          @click="
            setFilter('');
            value = null;
          "
        />
      </div>
      <div
        v-if="openProp"
        class="absolute z-10 w-full"
      >
        <ComboboxOptions
          class="scrollbar-styling absolute z-0 max-h-60 w-full overflow-y-auto overflow-x-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white pb-[100px] shadow-lg focus:outline-none"
          static
        >
          <li
            v-if="filteredEvents.length === 0"
            class="cursor-pointer truncate text-[14px] text-[#9CA3AF]"
          >
            <span>
              {{ t('menu.noEventFound') }}
            </span>
          </li>
          <ComboboxOption
            v-for="event in filteredEvents"
            :key="event.slug"
            v-slot="{ selected }"
            as="template"
            :value="event"
          >
            <li
              class="relative grid cursor-pointer grid-cols-[minmax(0,1fr)_36px] grid-rows-1 items-center text-left text-[14px] font-medium text-[#9CA3AF] hover:bg-[#FAFAFA] md:grid-cols-[minmax(0,1fr)_300px_36px]"
              :class="{ 'bg-[#F4F4F4]': selected }"
            >
              <span
                class="col-span-1 col-start-1 row-start-1 size-full truncate px-4 py-2"
                :class="selected ? 'font-medium' : 'font-normal'"
              >
                {{ event.title }}
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </div>
    </div>
  </Combobox>
</template>

<script setup lang="ts">
import useDetectClickOutside from '@/services/useDetectClickOutside';
import { type Event, useEvents } from '@/stores/eventsStore';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import { XIcon } from '@heroicons/vue/solid';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EventIcon from '../misc/EventIcon.vue';
import { EventParticipation } from '@/api/getDashboardData';

const { setFilter, filteredEvents } = useEvents();
const { locale, t } = useI18n();

const props = withDefaults(
  defineProps<{
    modelValue: EventParticipation | null;
  }>(),
  {
    modelValue: null
  }
);

const emit = defineEmits(['update:modelValue']);

const openProp = ref(false);
const combobox = ref<HTMLElement | null>(null);
const selectedEvent = ref<EventParticipation | null>(null);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    openProp.value = false;
    emit('update:modelValue', value);
  }
});

const titleStringRepr = computed(() => {
  return value.value
    .map((event) => {
      if (event !== null && event !== undefined) {
        return locale.value === 'en' ? event.titleEn : event.titleDe;
      }
      return '';
    })
    .join(', ');
});

function handleClick() {
  openProp.value = true;
  useDetectClickOutside(combobox, () => (openProp.value = false));
}
</script>

<style scoped>
.scrollbar-styling {
  scrollbar-width: none;
}

.scrollbar-styling::-webkit-scrollbar {
  display: none;
}
</style>
