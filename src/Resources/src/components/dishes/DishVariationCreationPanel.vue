<template>
  <form
    class="w-[300px] p-4 sm:w-[550px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="text-center">
      {{ edit ? t('dish.popover.variation.edit') : t('dish.popover.variation.create') }}
    </h3>
    <InputLabel
      v-model="titleDeInput"
      :label-text="t('dish.popover.german')"
      :required="required"
    />
    <InputLabel
      v-model="titleEnInput"
      :label-text="t('dish.popover.english')"
      :required="required"
    />
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { useDishes } from '@/stores/dishesStore';
import { CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import { ref } from 'vue';

const { t } = useI18n();
const { createDishVariation, updateDishVariation } = useDishes();

const props = withDefaults(
  defineProps<{
    titleDe?: string;
    titleEn?: string;
    slug?: string;
    parentSlug: string;
    edit?: boolean;
  }>(),
  {
    titleDe: '',
    titleEn: '',
    slug: null,
    edit: false
  }
);

const emit = defineEmits(['closePanel']);

const titleDeInput = ref(props.titleDe);
const titleEnInput = ref(props.titleEn);
const required = ref(false);

async function onSubmit() {
  required.value = true;
  if (titleDeInput.value === '' || titleEnInput.value === '') {
    return;
  } else if (props.edit === true && typeof props.slug === 'string') {
    await updateDishVariation(props.slug, createDishVariationDtoObject());
    emit('closePanel');
  } else if (props.edit === false) {
    await createDishVariation(createDishVariationDtoObject(), props.parentSlug);
    emit('closePanel');
  }
}

function createDishVariationDtoObject(): CreateDishVariationDTO {
  return {
    titleDe: titleDeInput.value,
    titleEn: titleEnInput.value
  };
}
</script>
