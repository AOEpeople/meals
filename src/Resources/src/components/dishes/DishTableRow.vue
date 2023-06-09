<template>
  <tr class="topBottomShadow border-b-2 border-gray-200">
    <td
      colspan="1"
      class="w-[60%] py-2"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ locale === 'en' ? dish.titleEn : dish.titleDe }}
      </span>
    </td>
    <td
      colspan="1"
      class="w-[10%]"
    >
      {{ getCategoryTitleById(dish.categoryId, locale) }}
    </td>
    <td
      colspan="1"
      class="w-[30%]"
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
    :class="index === 0 ? 'topShadow' : 'bottomShadow'"
  >
    <td
      colspan="2"
      class="w-[70%] py-2 pl-4"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ locale === 'en' ? variation.titleEn : variation.titleDe }}
      </span>
    </td>
    <td
      colspan="1"
      class="w-[30%]"
    >
      Actions for Variations
    </td>
  </tr>
</template>

<script setup lang="ts">
import { Dish } from '@/stores/dishesStore';
import { useI18n } from 'vue-i18n';
import { useCategories } from '@/stores/categoriesStore';
import DishActions from './DishActions.vue';

const { locale } = useI18n();
const { getCategoryTitleById } = useCategories();

defineProps<{
  dish: Dish,
  indexInList: number
}>();


</script>

<style scoped>
.topShadow {
  box-shadow: inset 0 0 3px 3px rgba(20,65,124,0.16);
  clip-path: inset(0px 10px 10px 10px);
  /* margin: 0 -8px; */
}

.bottomShadow {
  box-shadow: inset 0 0 3px 3px rgba(20,65,124,0.16);
  clip-path: inset(10px 10px 0px 10px);
  /* margin: 0 -8px; */
}
</style>