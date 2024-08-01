<template>
  <div class="z-[90] items-center xl:my-[42px] xl:grid xl:grid-cols-2">
    <div class="text-center xl:justify-self-start">
      <h2 class="m-0">
        {{ t('slot.header') }}
      </h2>
    </div>
    <div class="z-[99] xl:justify-self-end">
      <Popover
        :translate-x-min="'-20%'"
        class="m-auto w-fit"
      >
        <template #button="{ open }">
          <CreateButton
            :open="open"
            :btn-text="`+ ${t('slot.create')}`"
          />
        </template>
        <template #panel="{ close }">
          <SlotCreationPanel
            :id="undefined"
            :header="t('slot.createHeader')"
            :submit="t('slot.submit')"
            @closePanel="close()"
          />
        </template>
      </Popover>
    </div>
  </div>
</template>

<script setup lang="ts">
import Popover from '@/components/misc/Popover.vue';
import SlotCreationPanel from '@/components/timeslots/SlotCreationPanel.vue';
import { useI18n } from 'vue-i18n';
import CreateButton from '../misc/CreateButton.vue';

const { t } = useI18n();
</script>
