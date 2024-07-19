<template>
  <div
    v-if="finances !== null && finances.transactions !== undefined"
    class="w-full p-8"
  >
    <h1 class="mb-4 text-start">
      {{ finances.heading }}
    </h1>
    <FinanceTable
      :transactions="finances.transactions"
      :print="true"
      class="w-full"
    />
  </div>
</template>

<script setup lang="ts">
import FinanceTable from './FinanceTable.vue';
import { type Finances } from '@/stores/financesStore';

defineProps<{
  finances: Finances;
}>();
</script>
