<template>
  <Listbox
    v-slot="{ open }"
    v-model="selectedOption"
    as="span"
    class="relative w-full"
  >
    <div class="relative">
      <ListboxLabel class="w-full px-4 text-start text-xs font-medium text-[#173D7A]">
        <slot />
      </ListboxLabel>
      <ListboxButton
        :class="open ? 'rounded-t-[23px] border-x-2 border-t-2' : 'rounded-full border-2'"
        class="focus-visible:ring-offset-orange-300 flex w-full items-center border-[#CAD6E1] bg-white px-4 py-2 text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
      >
        <span class="w-full truncate text-[#9CA3AF]">
          {{ selectedOption.label }}
        </span>
        <ChevronDownIcon
          class="h-full w-5 justify-self-end text-[#9CA3AF]"
          :class="open ? 'rotate-180 transform' : ''"
          aria-hidden="true"
        />
      </ListboxButton>
      <div
        v-if="open"
        class="absolute z-10 w-full"
        data-cy="dropdown-options"
      >
        <ListboxOptions
          class="max-h-60 w-full overflow-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white shadow-lg focus:outline-none"
        >
          <ListboxOption
            v-for="option in listOptions"
            :key="option.label"
            v-slot="{ selected }"
            :value="option"
            class="cursor-pointer truncate text-[14px] text-[#9CA3AF] hover:bg-[#FAFAFA]"
          >
            <span
              class="inline-block size-full px-4 py-2"
              :class="selected ? 'bg-[#F4F4F4] font-medium' : 'font-normal'"
            >
              {{ option.label }}
            </span>
          </ListboxOption>
        </ListboxOptions>
      </div>
    </div>
  </Listbox>
</template>

<script setup lang="ts" generic="T">
import { Listbox, ListboxButton, ListboxOptions, ListboxOption, ListboxLabel } from '@headlessui/vue';
import { computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/solid';

export type IListOption<T> = {
  value: T;
  label: string;
};

const props = defineProps<{
  listOptions: IListOption<T>[];
  modelValue: IListOption<T>;
}>();

const emit = defineEmits(['update:modelValue']);

const selectedOption = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});
</script>
