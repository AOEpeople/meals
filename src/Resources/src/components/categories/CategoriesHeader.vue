<template>
  <div class="grid grid-rows-2 items-center xl:my-[42px] xl:grid-cols-2 xl:grid-rows-1">
    <h2 class="text-center xl:justify-self-start">
      {{ t('category.header') }}
    </h2>
    <Popover
      :translate-x-min="'-15%'"
      class="justify-self-center xl:justify-self-end"
    >
      <template #button="{ open }">
        <CreateButton
          :open="open"
          :btn-text="`+ ${t('category.create')}`"
        />
      </template>
      <template #panel="{ close }">
        <CategoriesCreationPanel @closePanel="close()" />
      </template>
    </Popover>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Popover from '../misc/Popover.vue';
import CreateButton from '../misc/CreateButton.vue';
import CategoriesCreationPanel from './CategoriesCreationPanel.vue';

const { t } = useI18n();
</script>
