<template>
  <tr class="border-b-2 border-gray-200">
    <td
      colspan="1"
      class="w-[50%] py-2"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ locale === 'en' ? dish.titleEn : dish.titleDe }}
      </span>
    </td>
    <td
      colspan="1"
      class="w-[10%] text-[12px] xl:text-[18px]"
    >
      {{ getCategoryTitleById(dish.categoryId, locale) }}
    </td>
    <td
      colspan="1"
      class="w-[40%]"
    >
      <DishActions
        :dish="dish"
        :index="indexInList"
      />
    </td>
  </tr>
  <tr
    v-for="(variation, index) in dish.variations"
    :key="variation.slug"
    class="overflow-hidden border-b-2 border-gray-200"
    :class="[index === 0 ? 'topShadow' : 'bottomShadow', dish.variations.length === 1 ? 'topBottomShadow' : '']"
  >
    <td
      colspan="1"
      class="w-[50%] py-2 pl-4"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ locale === 'en' ? variation.titleEn : variation.titleDe }}
      </span>
    </td>
    <td
      colspan="2"
      class="w-[50%]"
    >
      <DishVariationActions
        :variation="variation"
        :parent-slug="dish.slug"
      />
    </td>
  </tr>
</template>

<script setup lang="ts">
import { Dish } from '@/stores/dishesStore';
import { useI18n } from 'vue-i18n';
import { useCategories } from '@/stores/categoriesStore';
import DishActions from './DishActions.vue';
import DishVariationActions from './DishVariationActions.vue';

const { locale } = useI18n();
const { getCategoryTitleById } = useCategories();

defineProps<{
  dish: Dish,
  indexInList: number
}>();
</script>

<style scoped>

.topShadow {
  background-color: #f4f6f9;
  box-shadow: inset 0px 8px 6px -6px #e5e7eb;
}

.bottomShadow {
  background-color: #f4f6f9;
  box-shadow: inset 0px -8px 6px -6px #e5e7eb;
}

.topBottomShadow {
  background-color: #f4f6f9;
  box-shadow:inset 0 0 0 0 #e5e7eb , inset 0px -8px 6px -6px #e5e7eb, inset 0 0 0 0 #e5e7eb, inset 0px 8px 6px -6px #e5e7eb;
}
</style>