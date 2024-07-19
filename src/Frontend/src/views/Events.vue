<template>
  <div class="mx-[5%] xl:mx-auto">
    <EventsHeader v-model="filterInput" />
    <Table
      v-if="loaded"
      :labels="[t('event.table.title'), t('event.table.public'), t('event.table.actions')]"
    >
      <EventsTableRow
        v-for="event in filteredEvents"
        :key="event.id"
        :event="event"
      />
    </Table>
  </div>
  <LoadingSpinner :loaded="loaded" />
</template>

<script setup lang="ts">
import EventsHeader from '@/components/events/EventsHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import Table from '@/components/misc/Table.vue';
import EventsTableRow from '@/components/events/EventsTableRow.vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { fetchEvents, filteredEvents, setFilter } = useEvents();

const loaded = ref(false);

onMounted(async () => {
  const progress = useProgress().start();

  await fetchEvents();

  loaded.value = true;
  progress.finish();
});

const filterInput = ref('');

watch(
  () => filterInput.value,
  () => {
    setFilter(filterInput.value);
  }
);
</script>
