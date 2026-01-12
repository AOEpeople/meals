<template>
  <Table
    :labels="[
      t('prices.table.year'),
      t('prices.table.price'),
      t('prices.table.priceCombined'),
      t('prices.table.actions')
    ]"
    :print="print"
    :add-styles="'first:sticky first:left-0 first:bg-[#f4f7f9] last:pl-4'"
    :overflow-table="true"
  >
    <tr
      v-for="priceData in sortedPrices"
      :key="priceData.year"
      class="border-b-2 border-gray-200 text-[12px] xl:text-[18px]"
    >
      <td class="p-2">
        {{ priceData.year }}
      </td>
      <td class="p-2 text-left">
        {{
          new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: 'EUR'
          }).format(priceData.price)
        }}
      </td>
      <td class="p-2 text-left">
        {{
          new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: 'EUR'
          }).format(priceData.price_combined)
        }}
      </td>
      <td class="min-w-[100px] pl-2">
        <PricesTableActions
          :year="priceData.year"
          :highestYear="highestYearInSortedPrices"
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
        />
      </td>
    </tr>
  </Table>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import PricesTableActions from './PricesTableActions.vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t, locale } = useI18n();

interface PriceData {
  year: number;
  price: number;
  price_combined: number;
}

const props = withDefaults(
  defineProps<{
    prices: PriceData[];
    print?: boolean;
  }>(),
  {
    print: false
  }
);

defineEmits<{
  edit: [year: number];
  delete: [year: number];
}>();

const sortedPrices = computed(() => {
  return [...props.prices].sort((a, b) => b.year - a.year);
});
const highestYearInSortedPrices = computed(() => Math.max(...sortedPrices.value.map((p) => p.year)));
</script>
