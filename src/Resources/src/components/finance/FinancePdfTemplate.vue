<template>
  <div
    v-if="finances !== null && finances.transactions !== undefined"
    class="w-[700px]"
  >
    <h1 class="mb-4 text-start">
      {{ finances.heading }}
    </h1>
    <FinanceTable
      :transactions="finances.transactions"
      :print="true"
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
