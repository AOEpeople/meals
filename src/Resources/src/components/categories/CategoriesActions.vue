<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover :translate-x-min="'-50%'">
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          class="relative z-0 h-[40px]"
        />
      </template>
      <template #panel="{ close }">
        <CategoriesCreationPanel
          :edit="true"
          :index="index"
          :title-de="category.titleDe"
          :title-en="category.titleEn"
          @closePanel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
      @click="deleteCategoryWithSlug(category.slug)"
    />
  </div>
</template>

<script setup lang="ts">
import { Category } from '@/stores/categoriesStore';
import ActionButton from '../misc/ActionButton.vue';
import Popover from '../misc/Popover.vue';
import CategoriesCreationPanel from './CategoriesCreationPanel.vue';
import { Action } from '@/enums/Actions';
import { useI18n } from 'vue-i18n';
import { useCategories } from '@/stores/categoriesStore';

const { t } = useI18n();
const { deleteCategoryWithSlug } = useCategories();

defineProps<{
  category: Category;
  index: number;
}>();
</script>
