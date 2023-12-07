<template>
  <EventsHeader />
  <Table
    v-if="loaded"
    :labels="[t('event.table.title'), t('event.table.public'), t('event.table.actions')]"
  >
    <tr>
      <td>Example Event</td>
      <td>X</td>
      <td>
        <button>X</button>
      </td>
    </tr>
  </Table>
  <LoadingSpinner
    :loaded="loaded"
  />
</template>

<script setup lang="ts">
import EventsHeader from '@/components/events/EventsHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import Table from '@/components/misc/Table.vue';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const loaded = ref(false);

onMounted(() => {
  loaded.value = true;
});
</script>