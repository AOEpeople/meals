<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover>
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          class="relative z-0 h-[40px]"
        />
      </template>
      <template #panel="{ close }">
        <CategoriesCreationPanel />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
    />
  </div>
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import Popover from '../misc/Popover.vue';
import CategoriesCreationPanel from './CategoriesCreationPanel.vue';
import { Action } from '@/enums/Actions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

</script>