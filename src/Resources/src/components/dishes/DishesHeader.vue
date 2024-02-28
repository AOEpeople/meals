<template>
  <div class="grid grid-cols-1 grid-rows-3 items-center md:my-[42px] md:grid-cols-2 md:grid-rows-2">
    <h2 class="text-center md:justify-self-start">
      {{ t('dish.header') }}
    </h2>
    <Popover
      :breakpoint-width="768"
      :translate-x-max="'-65%'"
      :translate-x-min="'-15%'"
      :overflow-hidden="false"
      class="justify-self-center md:col-start-2 md:row-start-2 md:justify-self-end"
    >
      <template #button="{ open }">
        <CreateButton
          :open="open"
          :btn-text="`+ ${t('dish.create')}`"
        />
      </template>
      <template #panel="{ close }">
        <DishesCreationPanel @close-panel="close()" />
      </template>
    </Popover>
    <DishesSearchBar class="row-span-1 row-start-3 md:col-start-1 md:row-start-2 md:justify-self-start" />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Popover from '../misc/Popover.vue';
import CreateButton from '../misc/CreateButton.vue';
import DishesCreationPanel from './DishesCreationPanel.vue';
import DishesSearchBar from './DishesSearchBar.vue';

const { t } = useI18n();
</script>
