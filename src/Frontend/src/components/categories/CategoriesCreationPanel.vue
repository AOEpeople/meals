<template>
  <form
    class="w-[300px] p-4 sm:w-[400px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="w-full text-center">
      {{ edit ? t('category.popover.edit') : t('category.popover.create') }}
    </h3>
    <InputLabel
      v-model="titleDeInput"
      :label-text="t('category.popover.german')"
      :required="required"
    />
    <InputLabel
      v-model="titleEnInput"
      :label-text="t('category.popover.english')"
      :required="required"
      class="mb-5 mt-3"
    />
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { ref } from 'vue';
import { Category, useCategories } from '@/stores/categoriesStore';

const { t } = useI18n();
const { createCategory, editCategory } = useCategories();

const props = withDefaults(
  defineProps<{
    titleDe?: string;
    titleEn?: string;
    index?: number;
    edit?: boolean;
  }>(),
  {
    titleDe: '',
    titleEn: '',
    index: -1,
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
  }
  if (props.edit === true) {
    await editCategory(props.index, titleDeInput.value, titleEnInput.value);
  } else {
    const category: Category = {
      id: 0,
      titleDe: titleDeInput.value,
      titleEn: titleEnInput.value,
      slug: ''
    };
    await createCategory(category);
  }
  emit('closePanel');
}
</script>
