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
    <ListOptionsDropDown
      v-model="dietInput"
      :list-options="dietOptions"
      class="col-span-1 col-start-1 row-start-6 items-start pt-2 sm:col-span-1 sm:row-start-4"
      data-cy="veggi-options"
    >
      {{ t('dish.diet.diet') }}
    </ListOptionsDropDown>
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { useDishes } from '@/stores/dishesStore';
import { type CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import { computed, ref } from 'vue';
import { Diet } from '@/enums/Diet';
import ListOptionsDropDown from '@/components/misc/ListOptionsDropDown.vue';

const { t } = useI18n();
const { createDishVariation, updateDishVariation } = useDishes();

const props = withDefaults(
  defineProps<{
    titleDe?: string;
    titleEn?: string;
    slug?: string;
    parentSlug: string;
    edit?: boolean;
    diet?: Diet;
  }>(),
  {
    titleDe: '',
    titleEn: '',
    slug: undefined,
    edit: false,
    diet: Diet.MEAT
  }
);

const emit = defineEmits(['closePanel']);

const dietOptions = computed(() => {
  return [Diet.MEAT, Diet.VEGAN, Diet.VEGETARIAN].map((diet) => {
    return {
      value: diet,
      label: getDietOption(diet)
    };
  });
});

const titleDeInput = ref(props.titleDe);
const titleEnInput = ref(props.titleEn);
const dietInput = ref({
  value: props.diet,
  label: getDietOption(props.diet)
});
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
    titleEn: titleEnInput.value,
    diet: dietInput.value.value
  };
}

function getDietOption(diet: Diet): string {
  return t(`dish.diet.${diet}`);
}
</script>
