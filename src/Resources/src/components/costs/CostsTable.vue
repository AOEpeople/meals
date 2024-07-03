<template>
  <Table
    :labels="columnNames"
    :header-text-position="'lfr'"
    :add-styles="'first:sticky first:left-0 first:bg-[#f4f7f9] last:pl-4'"
    :overflow-table="true"
  >
    <LazyTableRow
      v-for="[username, costs] in filteredUsers"
      :key="username"
      :min-height="40"
      class="max-h-[62px] border-b-2 border-gray-200 text-right text-[12px] xl:text-[18px]"
    >
      <td class="sticky left-0 bg-[#f4f7f9] py-2 text-left">
        {{ `${costs.name}, ${costs.firstName}` }}
      </td>
      <td>
        {{ new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(costs.costs['earlier']) }}
      </td>
      <td
        v-for="column in Object.keys(CostsState.columnNames)"
        :key="`${String(column)}_${username}`"
      >
        {{ new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(costs.costs[column]) }}
      </td>
      <td>
        {{ new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(costs.costs['total']) }}
      </td>
      <td class="min-w-[100px] pl-2">
        <CostsTableActions
          :username="username"
          :balance="costs.costs['total']"
        />
      </td>
    </LazyTableRow>
  </Table>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import { useCosts } from '@/stores/costsStore';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CostsTableActions from './CostsTableActions.vue';
import LazyTableRow from '../misc/LazyTableRow.vue';

const { t, locale } = useI18n();
const { CostsState, getColumnNames } = useCosts();

const props = defineProps<{
  filter: string;
  showHidden: boolean;
}>();

const columnNames = computed(() => [
  'Name',
  t('costs.table.earlier'),
  ...getColumnNames(locale.value),
  t('costs.table.total'),
  t('costs.table.actions')
]);

const filterRegex = computed(() => {
  const filterStrings = props.filter.split(/[\s,.]+/).map((filterStr) => filterStr.toLowerCase());
  return createRegexForFilter(filterStrings);
});

const hiddenUsers = computed(() =>
  Object.entries(CostsState.users).filter((user) => {
    const value = user[1];
    return value.hidden === false || (value.hidden === true && props.showHidden === true);
  })
);

const filteredUsers = computed(() => {
  if (props.filter === '') {
    return hiddenUsers.value;
  }

  return hiddenUsers.value.filter((user) => {
    const value = user[1];
    const searchStrings = [value.firstName, value.name].join(' ');
    return filterRegex.value.test(searchStrings);
  });
});

function createRegexForFilter(filterStrings: string[]): RegExp {
  let regexStr = '';

  for (let i = 0; i < filterStrings.length - 1; i++) {
    regexStr += `(${filterStrings[i]}.*${filterStrings[i + 1]})?`;
    regexStr += `(${filterStrings[i + 1]}.*${filterStrings[i]})?`;
  }
  regexStr += `(${filterStrings.join('|')})`;

  if (filterStrings.length > 1) {
    regexStr = '^' + regexStr + '?$';
  }

  return new RegExp(regexStr, 'i');
}
</script>
