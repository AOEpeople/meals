<template>
  <Listbox
    v-slot="{ open }"
    v-model="selectedVariations"
    multiple
  >
    <span
      class="relative w-[300px]"
      :class="open ? 'z-10' : 'z-0'"
    >
      <ListboxButton
        class="relative w-full border-[#CAD6E1] bg-white px-4 py-2 text-center text-[14px] font-medium text-[#9CA3AF] focus:outline-none"
        :class="open ? 'rounded-t-[23px] border-x-2 border-t-2 border-b-[1px]' : 'rounded-full border-2'"
      >
        Variation
      </ListboxButton>
      <ListboxOptions
        class="scrollbar-styling absolute z-[100] max-h-60 w-full rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white shadow-lg focus:outline-none"
      >
        <ListboxOption
          v-for="(variation, index) in dish.variations"
          :key="variation.id"
          v-slot="{ selected }"
          :value="variation"
          class="cursor-pointer truncate text-[14px] text-[#9CA3AF] hover:bg-[#FAFAFA]"
          :class="index === dish.variations.length - 1 ? 'rounded-b-[23px]' : ''"
        >
          <span
            class="inline-block h-full w-full truncate px-4 py-2"
            :class="selected ? 'bg-[#F4F4F4] font-medium' : 'font-normal'"
          >
            {{ locale === 'en' ? variation.titleEn : variation.titleDe }}
          </span>
        </ListboxOption>
      </ListboxOptions>
    </span>
  </Listbox>
</template>

<script setup lang="ts">
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { Dish } from '@/stores/dishesStore';

const { locale } = useI18n();

const props = withDefaults(defineProps<{
  modelValue: Dish[] | null;
  dish: Dish;
}>(), {
  modelValue: null,
  dish: null
});

const emit = defineEmits(['update:modelValue']);

const selectedVariations = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});
</script>

<style scoped>
.scrollbar-styling {
  scrollbar-width: none;
}

.scrollbar-styling::-webkit-scrollbar {
  display: none;
}
</style>