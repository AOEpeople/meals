<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover :translate-x-min="'-71%'">
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          :hide-text-on-mobile="true"
        />
      </template>
      <template #panel="{ close }">
        <EventCreationPanel
          :title="event.title"
          :is-public="event.public"
          :edit="true"
          :slug="event.slug"
          @close-panel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :hide-text-on-mobile="true"
      @click="deleteEventWithSlug(event.slug)"
    />
  </div>
</template>

<script setup lang="ts">
import { Action } from '@/enums/Actions';
import ActionButton from '../misc/ActionButton.vue';
import Popover from '../misc/Popover.vue';
import { useI18n } from 'vue-i18n';
import EventCreationPanel from './EventCreationPanel.vue';
import { Event, useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { deleteEventWithSlug } = useEvents();

defineProps<{
  event: Event;
}>();
</script>
