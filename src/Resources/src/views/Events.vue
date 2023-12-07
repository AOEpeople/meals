<template>
  <EventsHeader />
  <Table
    v-if="loaded"
    :labels="[t('event.table.title'), t('event.table.public'), t('event.table.actions')]"
  >
    <EventsTableRow
      v-for="event in EventsState.events"
      :key="event.id"
      :event="event"
    />
  </Table>
  <LoadingSpinner
    :loaded="loaded"
  />
</template>

<script setup lang="ts">
import EventsHeader from '@/components/events/EventsHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import Table from '@/components/misc/Table.vue';
import EventsTableRow from '@/components/events/EventsTableRow.vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { fetchEvents, EventsState } = useEvents();

const loaded = ref(false);

onMounted(async () => {
  const progress = useProgress().start();

  await fetchEvents();

  loaded.value = true;
  progress.finish();
});
</script>