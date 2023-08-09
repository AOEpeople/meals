<template>
  <Table
    :labels="columnNames"
    :header-text-left="false"
  >
    <tr
      v-for="[username, costs] in filteredUsers"
      :key="username"
      class="max-h-[62px] border-b-2 border-gray-200 text-right text-[12px] xl:text-[18px]"
    >
      <td class="py-2 text-left">
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
      <td>
        Actions to be implemented
      </td>
    </tr>
  </Table>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import { useCosts } from '@/stores/costsStore';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const { CostsState, getColumnNames } = useCosts();

const props = defineProps<{
  filter: string
}>();

const columnNames = computed(() => ['Name', t('costs.table.earlier'), ...getColumnNames(locale.value), t('costs.table.total'), t('costs.table.actions')]);

const filteredUsers = computed(() => {
  if (props.filter === '') {
    return Object.entries(CostsState.users)
  }

  const filterStrings = props.filter.split(/[\s,.]+/).map(filterStr => filterStr.toLowerCase());
  const regex = createRegexForFilter(filterStrings);

  return Object.entries(CostsState.users).filter(user => {
    const [key, value] = user;
    const searchStrings = [value.firstName, value.name].join(' ');
    return regex.test(searchStrings);
  });
});

function createRegexForFilter(filterStrings: string[]): RegExp {
  let regexStr = '';

  for (let i = 0; i < filterStrings.length - 1; i++) {
    regexStr += `(${filterStrings[i]}.*${filterStrings[i+1]})?`;
    regexStr += `(${filterStrings[i+1]}.*${filterStrings[i]})?`;
  }
  regexStr += `(${filterStrings.join('|')})`;

  if (filterStrings.length > 1) {
    regexStr = '^' + regexStr + '?$';
  }

  return new RegExp(regexStr, 'i');
}
</script>