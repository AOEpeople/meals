<template>
  <div class="grid grid-cols-1 grid-rows-3 items-center md:my-[42px] md:grid-cols-2 md:grid-rows-2">
    <h2 class="text-center md:justify-self-start">
      {{ t('event.header') }}
    </h2>
    <Popover
      :breakpoint-width="768"
      :translate-x-max="'-60%'"
      :translate-x-min="'-20%'"
      class="justify-self-center md:col-start-2 md:row-start-2 md:justify-self-end"
    >
      <template #button="{ open }">
        <CreateButton
          :open="open"
          :btn-text="t('event.create')"
        />
      </template>
      <template #panel="{ close }">
        <EventCreationPanel @close-panel="close()" />
      </template>
    </Popover>
    <InputLabel
      v-model="filterInput"
      :label-text="t('event.search')"
      :label-visible="false"
      :x-button-active="true"
      class="row-span-1 row-start-3 md:col-start-1 md:row-start-2 md:justify-self-start"
    />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Popover from '../misc/Popover.vue';
import CreateButton from '../misc/CreateButton.vue';
import InputLabel from '../misc/InputLabel.vue';
import { computed } from 'vue';
import EventCreationPanel from './EventCreationPanel.vue';

const { t } = useI18n();

const props = defineProps<{
  modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);

const filterInput = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});
</script>
