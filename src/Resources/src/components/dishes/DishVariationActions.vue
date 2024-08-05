<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover
      :popup-styles="'right-0'"
      :translate-x-min="'-5%'"
      :translate-x-max="'-5%'"
    >
      <template #button>
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          :hide-text-on-mobile="true"
        />
      </template>
      <template #panel="{ close }">
        <DishVariationCreationPanel
          :parent-slug="parentSlug"
          :title-de="variation.titleDe"
          :title-en="variation.titleEn"
          :slug="variation.slug"
          :diet="variation.diet"
          :edit="true"
          @close-panel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
      :hide-text-on-mobile="true"
      @click="deleteDishVariationWithSlug(variation.slug)"
    />
  </div>
</template>

<script setup lang="ts">
import ActionButton from '@/components/misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import { useI18n } from 'vue-i18n';
import { useDishes } from '@/stores/dishesStore';
import { type Dish } from '@/stores/dishesStore';
import Popover from '../misc/Popover.vue';
import DishVariationCreationPanel from './DishVariationCreationPanel.vue';

const { t } = useI18n();
const { deleteDishVariationWithSlug } = useDishes();

defineProps<{
  variation: Dish;
  parentSlug: string;
}>();
</script>
