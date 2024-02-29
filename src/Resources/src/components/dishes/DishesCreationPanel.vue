<template>
  <form
    class="w-[300px] p-4 sm:w-[450px] md:w-[550px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="text-center">
      {{ edit ? t('dish.popover.edit') : t('dish.popover.create') }}
    </h3>
    <div class="z-0 grid w-full grid-cols-2 sm:gap-2">
      <InputLabel
        v-model="titleDeInput"
        :label-text="t('dish.popover.german')"
        :required="required"
        class="z-[1] col-span-2 row-start-1 sm:col-span-1 sm:col-start-1 sm:row-start-1"
      />
      <InputLabel
        v-model="titleEnInput"
        :label-text="t('dish.popover.english')"
        :required="required"
        class="z-[1] col-span-2 row-start-2 sm:col-span-1 sm:col-start-2 sm:row-start-1"
      />
      <InputLabel
        v-model="descriptionDeInput"
        :label-text="t('dish.popover.descriptionDe')"
        class="z-[1] col-span-2 row-start-3 sm:col-span-1 sm:col-start-1 sm:row-start-2"
      />
      <InputLabel
        v-model="descriptionEnInput"
        :label-text="t('dish.popover.descriptionEn')"
        class="z-[1] col-span-2 row-start-4 sm:col-span-1 sm:col-start-2 sm:row-start-2"
      />
      <CategoriesDropDown
        ref="categoryDropDown"
        :category-id="categoryId"
        class="z-[2] col-span-1 col-start-1 row-start-5 sm:row-start-3"
      />
      <SwitchGroup>
        <div class="col-span-1 col-start-2 row-start-5 flex flex-col items-start pt-2 sm:row-start-3">
          <SwitchLabel class="w-full px-4 text-start text-xs font-medium text-[#173D7A]">
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
      <SubmitButton class="z-[1] col-span-2 row-start-6 sm:col-start-1 sm:row-start-4" />
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
import Switch from '@/components/misc/Switch.vue';
import { SwitchGroup, SwitchLabel } from '@headlessui/vue';

const { t } = useI18n();
const { createDish, updateDish } = useDishes();
const { fetchCategories } = useCategories();

const props = withDefaults(
  defineProps<{
    titleDe?: string;
    titleEn?: string;
    descriptionDe?: string | null;
    descriptionEn?: string | null;
    categoryId?: number | null;
    oneSizeServing?: boolean;
    dishId?: number | null;
    edit?: boolean;
  }>(),
  {
    titleDe: '',
    titleEn: '',
    descriptionDe: null,
    descriptionEn: null,
    categoryId: null,
    oneSizeServing: false,
    dishId: null,
    edit: false
  }
);

const emit = defineEmits(['closePanel']);

onMounted(async () => {
  await fetchCategories();
});

const titleDeInput = ref(props.titleDe);
const titleEnInput = ref(props.titleEn);
const descriptionDeInput = ref(props.descriptionDe);
const descriptionEnInput = ref(props.descriptionEn);
const categoryDropDown = ref<InstanceType<typeof CategoriesDropDown> | null>(null);
const oneSizeServingInput = ref(props.oneSizeServing);
const required = ref(false);

async function onSubmit() {
  required.value = true;
  if (titleDeInput.value === '' || titleEnInput.value === '') {
    return;
  }
  if (props.edit === true) {
    await updateDish(props.dishId, createDishDtoObject());
  } else {
    await createDish(createDishDtoObject());
  }
  emit('closePanel');
}

function createDishDtoObject() {
  const dish: CreateDishDTO = {
    titleDe: titleDeInput.value,
    titleEn: titleEnInput.value,
    oneServingSize: oneSizeServingInput.value,
    descriptionDe: descriptionDeInput.value,
    descriptionEn: descriptionEnInput.value,
    category: categoryDropDown.value?.selectedCategory.id
  };
  return dish;
}

function setOneSizeServing(state: boolean) {
  oneSizeServingInput.value = state;
}
</script>
