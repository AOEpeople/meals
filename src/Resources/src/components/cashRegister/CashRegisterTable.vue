<template>
  <div>
    <h3>{{ dateRange }}</h3>
    <Table
      :labels="['Name', t('costs.amount'), t('costs.type')]"
      :header-text-position="'lfr'"
    >
      <tr
        v-for="[user, transaction] in Object.entries(transactions)"
        :key="user"
        class="max-h-[62px] border-b-2 border-gray-200 text-right text-[12px] xl:text-[18px]"
      >
        <td class="py-2 text-left">
          {{ `${(transaction as IUserTransaction).name}, ${(transaction as IUserTransaction).firstName}` }}
        </td>
        <td class="py-2 text-right">
          {{
            new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(
              parseFloat((transaction as IUserTransaction).amount)
            )
          }}
        </td>
        <td>
          <div class="flex size-full content-center justify-end text-primary">
            <svg
              v-if="
                (transaction as IUserTransaction).paymethod !== null &&
                (transaction as IUserTransaction).paymethod === '0'
              "
              version="1.1"
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              x="0px"
              y="0px"
              viewBox="0 0 1000 1000"
              xml:space="preserve"
              class="size-[24px]"
            >
              <g>
                <path
                  d="M918,239.3c-9.2-11.4-20-21.8-31.9-31.1c1.6,29.5-1.1,61-8.3,94.2c-19.4,90.9-65.8,168.2-134.2,223.5c-68.4,55.2-153.7,84.4-246.8,84.4H361l-44,205.9c-8.6,40.4-44.9,69.7-86.2,69.7h-61.2l-11,50.6c-2.8,13,0.4,26.6,8.7,37c8.4,10.4,21,16.4,34.3,16.4h149.6c20.8,0,38.8-14.5,43.1-34.9l51.4-240.8h171.4c82.9,0,158.7-25.8,219.1-74.6c60.5-48.8,101.6-117.4,118.8-198.4C972.7,359.5,960.2,291.5,918,239.3z"
                />
                <path
                  d="M273.9,807l51.4-240.8h171.4c82.9,0,158.7-25.8,219.1-74.6c60.5-48.8,101.6-117.4,118.8-198.4c17.5-81.9,5-149.8-37.2-202C757,41.1,687.7,10,616.7,10H243.2c-20.7,0-38.6,14.4-43.1,34.7l-162,743.8c-2.8,13,0.4,26.6,8.7,37c8.4,10.4,21,16.4,34.3,16.4h149.6C251.6,841.9,269.6,827.4,273.9,807z M411.1,179.4h117.5c27.7,0,52.4,11.6,67.8,31.8c16.4,21.5,21.2,51.1,13.2,81.4c-0.1,0.4-0.2,0.8-0.3,1.2C594.5,356.2,533,407,472.3,407H359.7L411.1,179.4z"
                />
              </g>
            </svg>
            <CurrencyEuroIcon
              v-else
              class="size-8"
            />
          </div>
        </td>
      </tr>
      <tr class="max-h-[62px] border-b-2 border-gray-200 text-right text-[12px] xl:text-[18px]">
        <td class="py-2 text-left font-bold">
          {{ t('costs.table.total') }}
        </td>
        <td class="py-2 text-right font-bold">
          {{ new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(getTotalAmount()) }}
        </td>
        <td />
      </tr>
    </Table>
  </div>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import { type IUserTransaction } from '@/stores/accountingStore';
import { type Dictionary } from '@/types/types';
import { useI18n } from 'vue-i18n';
import { CurrencyEuroIcon } from '@heroicons/vue/outline';

const { t, locale } = useI18n();

const props = defineProps<{
  transactions: Dictionary<IUserTransaction>;
  dateRange: string;
}>();

function getTotalAmount() {
  return Object.values(props.transactions)
    .map((transaction) => parseFloat((transaction as IUserTransaction).amount))
    .reduce((total, transaction) => total + transaction, 0);
}
</script>
