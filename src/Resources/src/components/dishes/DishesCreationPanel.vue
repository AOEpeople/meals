<template>
  <form
    class="w-[300px] p-4 sm:w-[400px]"
    @submit.prevent="onSubmit"
  >
    <h3>
      {{ edit ? t('dish.popover.edit') : t('dish.popover.create') }}
    </h3>
    <div class="grid">
      <InputLabel
        v-model="titleDeInput"
        :label-text="t('dish.popover.german')"
      />
      <InputLabel
        v-model="titleEnInput"
        :label-text="t('dish.popover.english')"
      />
      <InputLabel
        v-model="descriptionDeInput"
        :label-text="t('dish.popover.descriptionDe')"
      />
      <InputLabel
        v-model="descriptionDeInput"
        :label-text="t('dish.popover.descriptionEn')"
      />
      <span>Category to be implemented</span>
      <span>OneServingSize to be implemented</span>
    </div>
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { Dish, useDishes } from '@/stores/dishesStore';
import { ref } from 'vue';
import { CreateDishDTO } from '@/api/postCreateDish';

const { t } = useI18n();
const { createDish } = useDishes();

const props = withDefaults(defineProps<{
  titleDe?: string,
  titleEn?: string,
  descriptionDe?: string,
  descriptionEn?: string,
  categoryId?: number,
  oneSizeServing?: boolean,
  index?: number,
  edit?: boolean
}>(),{
  titleDe: '',
  titleEn: '',
  descriptionDe: null,
  descriptionEn: null,
  categoryId: null,
  oneSizeServing: false,
  index: null,
  edit: false
});

const titleDeInput = ref(props.titleDe);
const titleEnInput = ref(props.titleEn);
const descriptionDeInput = ref(props.descriptionDe);
const descriptionEnInput = ref(props.descriptionEn);
const categoryIdInput = ref(props.categoryId);
const oneSizeServingInput = ref(props.oneSizeServing);

async function onSubmit() {
  if (titleDeInput.value === '' || titleEnInput.value === '') {
    return;
  }
  if (props.edit) {

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
    category: categoryIdInput.value
  }
  return dish;
}
</script>