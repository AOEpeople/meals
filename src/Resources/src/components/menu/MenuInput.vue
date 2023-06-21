<template>
  <Combobox
    v-model="selectedDish"
    as="span"
    nullable
  >
    <div
      class="flex w-full flex-row items-center overflow-hidden border-[#CAD6E1] bg-white px-4 py-2 text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
      :class="openProp ? 'rounded-t-[23px] border-x-2 border-t-2' : 'rounded-full border-2'"
    >
      <ComboboxInput
        ref="comboboxInput"
        :displayValue="// @ts-ignore
          (dish) => locale === 'en' ? dish?.titleEn : dish?.titleDe"
        class="w-full truncate border-none text-[#9CA3AF] focus-visible:hidden"
        @change="query = $event.target.value"
        @click="handleClick"
      />
      <XIcon
        class="h-full w-5 justify-self-end text-[#9CA3AF]"
        aria-hidden="true"
        @click="selectedDish = null; query = ''"
      />
    </div>
    <div v-show="openProp">
      <ComboboxOptions
        class="scrollbar-styling max-h-60 w-full overflow-y-auto overflow-x-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white shadow-lg focus:outline-none"
        static
      >
        <li
          v-if="filteredDishes.length === 0 && query !== ''"
          class="cursor-pointer truncate text-[14px] text-[#9CA3AF]"
        >
          Nothing found
        </li>
        <ComboboxOption
          v-for="dish in filteredDishes"
          :key="dish.id"
          v-slot="{ selected }"
          as="template"
          :value="dish"
          @click="openProp = false"
        >
          <li
            class="flex cursor-pointer flex-row items-center truncate text-left text-[14px] font-medium text-[#9CA3AF] hover:bg-[#FAFAFA]"
            :class="{ 'bg-[#F4F4F4]': selected }"
          >
            <span
              class="h-full w-full px-4 py-2"
              :class="selected ? 'font-medium' : 'font-normal'"
            >
              {{ locale === 'en' ? dish.titleEn : dish.titleDe }}
            </span>
            <CheckIcon
              v-if="selected"
              class="h-full w-5 justify-self-end text-[#9CA3AF]"
              aria-hidden="true"
            />
          </li>
        </ComboboxOption>
      </ComboboxOptions>
    </div>
  </Combobox>
</template>

<script setup lang="ts">
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import { useDishes } from '@/stores/dishesStore';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';
import { CheckIcon, XIcon } from '@heroicons/vue/solid';
import useDetectClickOutside from '@/services/useDetectClickOutside';

const { DishesState } = useDishes();
const { locale } = useI18n();

const comboboxInput = ref<HTMLElement | null>(null);
const selectedDish = ref(null);
const query = ref('');
const openProp = ref(false);

const filteredDishes = computed(() => {
   return query.value === '' ?
    DishesState.dishes :
    DishesState.dishes.filter(dish => dish.titleDe.toLocaleLowerCase().includes(query.value.toLocaleLowerCase()));
});

function handleClick() {
  openProp.value = true;
  useDetectClickOutside(comboboxInput, () => openProp.value = false);
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