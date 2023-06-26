<template>
  <Combobox
    v-slot="{ open }"
    v-model="selectedDish"
    as="span"
    class="relative w-full"
    nullable
  >
    <div
      ref="combobox"
      class="relative w-full"
      @click="handleClick"
    >
      <div
        class="flex w-full flex-row items-center overflow-hidden border-[#CAD6E1] bg-white text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
        :class="openProp ? 'rounded-t-[23px] border-x-2 border-t-2 border-b-[1px]' : 'rounded-full border-2'"
      >
        <ComboboxInput
          :displayValue="// @ts-ignore
            (dish) => locale === 'en' ? dish?.titleEn : dish?.titleDe"
          class="w-full truncate border-none px-4 py-2 text-[#9CA3AF] focus:outline-none"
          @change="query = $event.target.value"
        />
        <XIcon
          class="mr-4 h-full w-10 cursor-pointer justify-self-end px-1 py-2 text-[#9CA3AF] transition-transform hover:scale-[120%]"
          aria-hidden="true"
          @click="value = null; query = ''; selectedVariations = []"
        />
      </div>
      <div
        v-show="openProp"
        class="absolute z-10 w-full"
      >
        <ComboboxOptions
          class="scrollbar-styling absolute z-[0] max-h-60 w-full overflow-y-auto overflow-x-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white pb-[100px] shadow-lg focus:outline-none"
          static
        >
          <li
            v-if="filteredDishes.length === 0 && query !== ''"
            class="cursor-pointer truncate text-[14px] text-[#9CA3AF]"
          >
            <span class="h-full w-full px-4 py-2">
              {{ t('menu.noDishFound') }}
            </span>
          </li>
          <ComboboxOption
            v-for="dish in filteredDishes"
            :key="dish.id"
            v-slot="{ selected }"
            as="template"
            :value="dish"
          >
            <li
              class="relative grid cursor-pointer grid-cols-[minmax(0,_1fr)_300px_36px] items-center text-left text-[14px] font-medium text-[#9CA3AF] hover:bg-[#FAFAFA]"
              :class="{ 'bg-[#F4F4F4]': selected }"
            >
              <span
                class="h-full w-full truncate px-4 py-2"
                :class="selected ? 'font-medium' : 'font-normal'"
              >
                {{ locale === 'en' ? dish.titleEn : dish.titleDe }}
              </span>
              <MenuDishVariationsCombobox
                v-if="dish.variations.length > 0 && selected"
                v-model="selectedVariations"
                :dish=" // @ts-ignore
                  (dish as Dish)"
              />
              <span
                v-if="dish.variations.length > 0 && !selected"
                class="relative w-full px-4 py-2 text-center text-[14px] font-medium text-[#9CA3AF] focus:outline-none"
              >
                Variation
              </span>
              <CheckIcon
                v-if="selected"
                class="col-start-3 mr-4 h-full w-5 justify-self-end text-[#9CA3AF]"
                aria-hidden="true"
              />
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </div>
    </div>
  </Combobox>
</template>

<script setup lang="ts">
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import { Dish, useDishes } from '@/stores/dishesStore';
import { useI18n } from 'vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';
import { CheckIcon, XIcon } from '@heroicons/vue/solid';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import MenuDishVariationsCombobox from './MenuDishVariationsCombobox.vue';

const { DishesState } = useDishes();
const { locale, t } = useI18n();

const props = withDefaults(defineProps<{
  modelValue: Dish[] | null;
}>(), {
  modelValue: null
});

const emit = defineEmits(['update:modelValue']);

const combobox = ref<HTMLElement | null>(null);
const query = ref('');
const openProp = ref(false);
const selectedVariations = ref([]);
const selectedDish = ref<Dish | null>(null);

onMounted(() => {
  // set initial value for dish
  if (props.modelValue?.length > 0) {
    props.modelValue.forEach(dish => {
      if (dish.parentId === null) {
        selectedDish.value = dish;
      }
    });
    // set initial value for variations (after dish is set, otherwise the watcher empties the array)
    props.modelValue.forEach(dish => {
      if (dish.parentId !== null) {
        selectedVariations.value.push(dish);
      }
    });
  }
});

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const filteredDishes = computed(() => {
   return query.value === '' ?
    DishesState.dishes :
    DishesState.dishes.filter(dish => dish.titleDe.toLocaleLowerCase().includes(query.value.toLocaleLowerCase()));
});

// empty the array of variations when a dish is selected
watch(
  () => selectedDish.value,
  () => {
    selectedVariations.value = [];
    value.value = [selectedDish.value];
  }
);

watch(
  () => selectedVariations.value,
  () => {
    value.value = [selectedDish.value, ...selectedVariations.value];
  }
);

function handleClick() {
  openProp.value = true;
  useDetectClickOutside(combobox, () => openProp.value = false);
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