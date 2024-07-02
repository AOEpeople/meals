<template>
  <Table
    :labels="['Name', t('costs.table.total')]"
    :header-text-position="'lfr'"
    :overflow-table="false"
  >
    <LazyTableRow
      v-for="[username, costs] in mealsUsers"
      :key="username"
      :min-height="40"
      class="max-h-[40px] border-b-2 border-gray-200 text-right text-[12px] xl:text-[18px]"
    >
      <td class="py-2 text-left">
        {{ `${costs.name}, ${costs.firstName}` }}
      </td>
      <td>
        {{
          new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(parseFloat(costs.costs['total']))
        }}
      </td>
    </LazyTableRow>
  </Table>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import LazyTableRow from '../misc/LazyTableRow.vue';
import { useI18n } from 'vue-i18n';
import { useCosts } from '@/stores/costsStore';
import { computed } from 'vue';

const { t, locale } = useI18n();
const { CostsState } = useCosts();

const mealsUsers = computed(() => Object.entries(CostsState.users).filter((user) => !user[1].hidden));
</script>
