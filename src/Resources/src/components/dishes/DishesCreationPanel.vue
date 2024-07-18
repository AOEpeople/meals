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
        v-model="dishInput.titleDe"
        :label-text="t('dish.popover.german')"
        :required="required"
        class="z-[1] col-span-2 row-start-1 sm:col-span-1 sm:col-start-1 sm:row-start-1"
      />
      <InputLabel
        v-model="dishInput.titleEn"
        :label-text="t('dish.popover.english')"
        :required="required"
        class="z-[1] col-span-2 row-start-2 sm:col-span-1 sm:col-start-2 sm:row-start-1"
      />
      <InputLabel
        v-model="dishInput.descriptionDe"
        :label-text="t('dish.popover.descriptionDe')"
        class="z-[1] col-span-2 row-start-3 sm:col-span-1 sm:col-start-1 sm:row-start-2"
      />
      <InputLabel
        v-model="dishInput.descriptionEn"
        :label-text="t('dish.popover.descriptionEn')"
        class="z-[1] col-span-2 row-start-4 sm:col-span-1 sm:col-start-2 sm:row-start-2"
      />
      <CategoriesDropDown
        ref="categoryDropDown"
        :category-id="categoryId"
        class="col-span-1 col-start-1 row-start-5 sm:row-start-3"
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
            @toggle="(value) => (dishInput.oneServingSize = value)"
          />
        </div>
      </SwitchGroup>
      <ListOptionsDropDown
        v-model="dietInput"
        :list-options="dietOptions"
        class="col-span-1 col-start-1 row-start-6 items-start pt-2 sm:col-span-1 sm:row-start-4"
        data-cy="veggi-options"
      >
        {{ t('dish.diet.diet') }}
      </ListOptionsDropDown>
      <SubmitButton class="relative col-span-2 row-start-7 sm:col-start-1 sm:row-start-5" />
    </div>
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { useDishes } from '@/stores/dishesStore';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { CreateDishDTO } from '@/api/postCreateDish';
import CategoriesDropDown from '../categories/CategoriesDropDown.vue';
import { useCategories } from '@/stores/categoriesStore';
import Switch from '@/components/misc/Switch.vue';
import { SwitchGroup, SwitchLabel } from '@headlessui/vue';
import ListOptionsDropDown from '@/components/misc/ListOptionsDropDown.vue';
import { Diet } from '@/enums/Diet';

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
    diet?: Diet;
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
    diet: Diet.MEAT,
    dishId: null,
    edit: false
  }
);

const emit = defineEmits(['closePanel']);

onMounted(async () => {
  await fetchCategories();
});

const dietOptions = computed(() => {
  return [Diet.MEAT, Diet.VEGAN, Diet.VEGETARIAN].map((diet) => {
    return {
      value: diet,
      label: getDietOption(diet)
    };
  });
});
const dietInput = ref({
  value: props.diet,
  label: getDietOption(props.diet)
});

const categoryDropDown = ref<InstanceType<typeof CategoriesDropDown> | null>(null);

const required = ref(false);

const dishInput = reactive<CreateDishDTO>({
  titleDe: props.titleDe,
  titleEn: props.titleEn,
  descriptionDe: props.descriptionDe,
  descriptionEn: props.descriptionEn,
  oneServingSize: props.oneSizeServing,
  diet: props.diet,
  category: null
});

watch(
  () => categoryDropDown.value?.selectedCategory.id,
  () => {
    dishInput.category = categoryDropDown.value?.selectedCategory.id;
  }
);

watch(
  () => dietInput.value,
  () => {
    dishInput.diet = dietInput.value.value;
  }
);

async function onSubmit() {
  required.value = true;
  if (dishInput.titleDe === '' || dishInput.titleEn === '') {
    return;
  }
  if (props.edit === true) {
    await updateDish(props.dishId, dishInput);
  } else {
    await createDish(dishInput);
  }
  emit('closePanel');
}

function getDietOption(diet: Diet): string {
  return t(`dish.diet.${diet}`);
}
</script>
