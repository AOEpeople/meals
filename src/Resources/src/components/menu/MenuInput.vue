<template>
  <Combobox
    v-model="selectedDish"
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
        <MealIcon
          class="ml-4 aspect-square h-full"
          :fill-colour="'fill-[#9CA3AF]'"
        />
        <ComboboxInput
          :displayValue="() => titleStringRepr"
          class="w-full truncate border-none px-4 py-2 text-[#9CA3AF] focus:outline-none"
          @change="setFilter($event.target.value)"
        />
        <XIcon
          class="mr-4 h-full w-10 cursor-pointer justify-self-end px-1 py-2 text-[#9CA3AF] transition-transform hover:scale-[120%]"
          aria-hidden="true"
          @click="
            setFilter('');
            selectedDish = null;
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
            v-if="filteredDishes.length === 0"
            class="cursor-pointer truncate text-[14px] text-[#9CA3AF]"
          >
            <span class="size-full px-4 py-2">
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
              class="relative grid cursor-pointer grid-cols-[minmax(0,1fr)_36px] grid-rows-[minmax(0,1fr)_auto] items-center text-left text-[14px] font-medium text-[#9CA3AF] hover:bg-[#FAFAFA] md:grid-cols-[minmax(0,1fr)_300px_36px] md:grid-rows-1"
              :class="{ 'bg-[#F4F4F4]': selected }"
              @click="handleSelection(dish)"
            >
              <span
                class="col-span-1 col-start-1 row-start-1 size-full truncate px-4 py-2"
                :class="selected ? 'font-medium' : 'font-normal'"
              >
                {{ locale === 'en' ? dish.titleEn : dish.titleDe }}
              </span>
              <MenuDishVariationsCombobox
                v-if="dish.variations.length > 0 && selected && loadingFinished"
                v-model="selectedVariations"
                :dish="
                  // @ts-ignore
                  dish as Dish
                "
                class="col-span-2 col-start-1 row-start-2 justify-self-center md:col-span-1 md:col-start-2 md:row-start-1"
              />
              <span
                v-if="dish.variations.length > 0 && !selected"
                class="relative col-span-2 col-start-1 row-start-2 w-full px-4 py-2 text-center text-[14px] font-medium text-[#9CA3AF] focus:outline-none md:col-span-1 md:col-start-2 md:row-start-1"
              >
                Variation
              </span>
              <div
                v-if="MenuCountState.counts[dish.id] && MenuCountState.counts[dish.id] > 0"
                class="col-start-2 row-start-1 mr-4 flex size-6 items-center justify-center self-center justify-self-end rounded-lg bg-cyan text-center text-white md:col-start-3"
                aria-hidden="true"
              >
                {{ MenuCountState.counts[dish.id] }}
              </div>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </div>
    </div>
  </Combobox>
</template>

<script setup lang="ts">
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import { type Dish, useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useI18n } from 'vue-i18n';
import { type WatchStopHandle, computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { XIcon } from '@heroicons/vue/solid';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import MenuDishVariationsCombobox from './MenuDishVariationsCombobox.vue';
import MealIcon from './MealIcon.vue';

const { setFilter, filteredDishes } = useDishes();
const { locale, t } = useI18n();
const { MenuCountState } = useWeeks();

const props = withDefaults(
  defineProps<{
    modelValue: Dish[] | null;
  }>(),
  {
    modelValue: null
  }
);

const emit = defineEmits(['update:modelValue']);

const combobox = ref<HTMLElement | null>(null);
const openProp = ref(false);
const selectedVariations = ref<Dish[]>([]);
const selectedDish = ref<Dish | null>(null);
const loadingFinished = ref<boolean>(false);
let unwatch: WatchStopHandle;

onMounted(() => {
  // set initial value for dish
  if (props.modelValue?.length ?? 0 > 0) {
    props.modelValue?.forEach((dish) => {
      if (dish.parentId === null) {
        selectedDish.value = dish;
      }
    });
    // set initial value for variations (after dish is set, otherwise the watcher empties the array)
    props.modelValue?.forEach((dish) => {
      if (dish.parentId !== null) {
        selectedVariations.value.push(dish);
      }
    });
  }
  loadingFinished.value = true;

  unwatch = watch(selectedDish, (newSelectedDish, oldSelectedDish) => {
    if (
      loadingFinished.value !== null &&
      loadingFinished.value !== undefined &&
      newSelectedDish?.id !== oldSelectedDish?.id
    ) {
      selectedVariations.value = [];
      value.value = newSelectedDish ? [newSelectedDish] : [];
    }
  });

  setFilter('');
});

onUnmounted(() => {
  unwatch();
});

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

watch(selectedVariations, (newSelctedVariations, oldSelectedVariations) => {
  if (
    selectedDish.value !== null &&
    loadingFinished.value !== null &&
    loadingFinished.value !== undefined &&
    newSelctedVariations.length !== oldSelectedVariations.length
  ) {
    value.value = [selectedDish.value, ...selectedVariations.value];
  }
});

const titleStringRepr = computed(() => {
  return value.value?.map((dish) => {
      if (dish !== null && dish !== undefined) {
        return locale.value === 'en' ? dish.titleEn : dish.titleDe;
      }
      return '';
    })
    .join(', ') ?? '';
});

function handleClick() {
  openProp.value = true;
  useDetectClickOutside(combobox, () => (openProp.value = false));
}

function handleSelection(dish: Dish) {
  selectedDish.value = dish;
  if (dish.variations.length === 0) {
    openProp.value = false;
  }
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
