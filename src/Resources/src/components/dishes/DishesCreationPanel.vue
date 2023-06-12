<template>
  <form
    class="w-[300px] p-4 sm:w-[550px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="text-center">
      {{ edit ? t('dish.popover.edit') : t('dish.popover.create') }}
    </h3>
    <div class="z-0 grid gap-2 sm:grid-cols-2">
      <InputLabel
        v-model="titleDeInput"
        :label-text="t('dish.popover.german')"
        class="z-[1] sm:col-span-1 sm:col-start-1"
      />
      <InputLabel
        v-model="titleEnInput"
        :label-text="t('dish.popover.english')"
        class="z-[1] sm:col-span-1 sm:col-start-2"
      />
      <InputLabel
        v-model="descriptionDeInput"
        :label-text="t('dish.popover.descriptionDe')"
        class="z-[1] sm:col-span-1 sm:col-start-1"
      />
      <InputLabel
        v-model="descriptionEnInput"
        :label-text="t('dish.popover.descriptionEn')"
        class="z-[1] sm:col-span-1 sm:col-start-2"
      />
      <CategoriesDropDown
        ref="categoryDropDown"
        :category-id="categoryId"
        class="z-[2] sm:col-span-1 sm:col-start-1"
      />
      <SwitchGroup>
        <div class="flex flex-col items-start pt-2">
          <SwitchLabel class="w-fulltext-start px-4 text-xs font-medium text-[#173D7A]">
            {{ t('dish.popover.oneSizeServing') }}
          </SwitchLabel>
          <Switch
            :sr="t('dish.popover.oneSizeServing')"
            :initial="oneSizeServing"
            class="my-auto ml-4"
            @toggle="(value) => setOneSizeServing(value)"
          />
        </div>
      </SwitchGroup>
      <SubmitButton
        class="z-[1] sm:col-span-2 sm:col-start-1"
      />
    </div>
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { useDishes } from '@/stores/dishesStore';
import { onMounted, ref } from 'vue';
import { CreateDishDTO } from '@/api/postCreateDish';
import CategoriesDropDown from '../categories/CategoriesDropDown.vue';
import { useCategories } from '@/stores/categoriesStore';
import Switch from "@/components/misc/Switch.vue"
import { SwitchGroup, SwitchLabel } from '@headlessui/vue';

const { t } = useI18n();
const { createDish, updateDish } = useDishes();
const { fetchCategories } = useCategories();

const props = withDefaults(defineProps<{
  titleDe?: string,
  titleEn?: string,
  descriptionDe?: string,
  descriptionEn?: string,
  categoryId?: number,
  oneSizeServing?: boolean,
  dishId?: number,
  edit?: boolean,
}>(),{
  titleDe: '',
  titleEn: '',
  descriptionDe: null,
  descriptionEn: null,
  categoryId: null,
  oneSizeServing: false,
  dishId: null,
  edit: false
});

onMounted(async () => {
  await fetchCategories();
});

const titleDeInput = ref(props.titleDe);
const titleEnInput = ref(props.titleEn);
const descriptionDeInput = ref(props.descriptionDe);
const descriptionEnInput = ref(props.descriptionEn);
const categoryDropDown = ref<InstanceType<typeof CategoriesDropDown> | null>(null);
const oneSizeServingInput = ref(props.oneSizeServing);

async function onSubmit() {
  if (titleDeInput.value === '' || titleEnInput.value === '') {
    return;
  }
  if (props.edit) {
    await updateDish(props.dishId, createDishDtoObject());
  } else {
    await createDish(createDishDtoObject());
  }
}

function createDishDtoObject() {
  const dish: CreateDishDTO = {
    titleDe: titleDeInput.value,
    titleEn: titleEnInput.value,
    oneServingSize: oneSizeServingInput.value,
    descriptionDe: descriptionDeInput.value,
    descriptionEn: descriptionEnInput.value,
    category: categoryDropDown.value?.selectedCategory.id
  }
  return dish;
}

function setOneSizeServing(state: boolean) {
  oneSizeServingInput.value = state;
}
</script>