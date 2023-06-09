<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover>
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('dish.popover.edit')"
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
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
      @click="deleteDishWithSlug(dish.slug)"
    />
  </div>
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import { useI18n } from 'vue-i18n';
import { Action } from '@/enums/Actions';
import { useDishes } from '@/stores/dishesStore';
import { Dish } from '@/stores/dishesStore';
import Popover from '../misc/Popover.vue';
import DishesCreationPanel from './DishesCreationPanel.vue';

const { t } = useI18n();
const { deleteDishWithSlug } = useDishes();

defineProps<{
  dish: Dish,
  index: number
}>();
</script>