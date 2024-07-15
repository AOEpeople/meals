<template>
  <Listbox
    v-slot="{ open }"
    v-model="selectedCategory"
    as="span"
    class="relative w-full"
  >
    <ListboxLabel class="w-full px-4 text-start text-xs font-medium text-[#173D7A]">
      {{ t('dish.popover.category') }}
    </ListboxLabel>
    <ListboxButton
      :class="open ? 'rounded-t-[23px] border-x-2 border-t-2' : 'rounded-full border-2'"
      class="focus-visible:ring-offset-orange-300 flex w-full items-center border-[#CAD6E1] bg-white px-4 py-2 text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
    >
      <span class="w-full truncate text-[#9CA3AF]">
        {{ locale === 'en' ? selectedCategory.titleEn : selectedCategory.titleDe }}
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
    >
      <ListboxOptions
        class="max-h-60 w-full overflow-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white shadow-lg focus:outline-none"
      >
        <ListboxOption
          v-for="category in CategoriesState.categories"
          :key="category.id"
          v-slot="{ selected }"
          :value="category"
          class="cursor-pointer truncate text-[14px] text-[#9CA3AF] hover:bg-[#FAFAFA]"
        >
          <span
            class="inline-block size-full px-4 py-2"
            :class="selected ? 'bg-[#F4F4F4] font-medium' : 'font-normal'"
          >
            {{ locale === 'en' ? category.titleEn : category.titleDe }}
          </span>
        </ListboxOption>
      </ListboxOptions>
    </div>
  </Listbox>
</template>

<script setup lang="ts">
import { Listbox, ListboxButton, ListboxOptions, ListboxOption, ListboxLabel } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';
import { Category, useCategories } from '@/stores/categoriesStore';
import { ChevronDownIcon } from '@heroicons/vue/solid';
import { Ref, computed, ref } from 'vue';

const { t, locale } = useI18n();
const { CategoriesState, getCategoryById } = useCategories();

const props = withDefaults(
  defineProps<{
    categoryId?: number;
  }>(),
  {
    categoryId: null
  }
);

const initialCategory = computed(() =>
  props.categoryId ? getCategoryById(props.categoryId) : CategoriesState.categories[0]
);
const selectedCategory: Ref<Category> = ref(initialCategory.value);

defineExpose({ selectedCategory });
</script>
