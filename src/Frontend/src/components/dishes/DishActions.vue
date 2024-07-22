<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover
      :popup-styles="'right-0'"
      :translate-x-min="'-5%'"
      :translate-x-max="'-5%'"
    >
      <template #button="{ open }">
        <ActionButton
          :action="Action.CREATE"
          :btn-text="t('dish.popover.variation.new')"
          :hide-text-on-mobile="true"
        />
      </template>
      <template #panel="{ close }">
        <DishVariationCreationPanel
          :parent-slug="dish.slug"
          @close-panel="close()"
        />
      </template>
    </Popover>
    <Popover
      :popup-styles="'right-0'"
      :translate-x-min="'-5%'"
      :translate-x-max="'-5%'"
    >
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          :hide-text-on-mobile="true"
        />
      </template>
      <template #panel="{ close }">
        <DishesCreationPanel
          :edit="true"
          :title-de="dish.titleDe"
          :title-en="dish.titleEn"
          :description-de="dish.descriptionDe"
          :description-en="dish.descriptionEn"
          :dish-id="dish.id"
          :category-id="dish.categoryId"
          :one-size-serving="dish.oneServingSize"
          @close-panel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
      :hide-text-on-mobile="true"
      @click="deleteDishWithSlug(dish.slug)"
    />
  </div>
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import { useI18n } from 'vue-i18n';
import { Action } from '@/enums/Actions';
import { useDishes } from '@/stores/dishesStore';
import { type Dish } from '@/stores/dishesStore';
import Popover from '../misc/Popover.vue';
import DishesCreationPanel from './DishesCreationPanel.vue';
import DishVariationCreationPanel from './DishVariationCreationPanel.vue';

const { t } = useI18n();
const { deleteDishWithSlug } = useDishes();

defineProps<{
  dish: Dish;
  index: number;
}>();
</script>
