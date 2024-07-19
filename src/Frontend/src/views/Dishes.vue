<template>
  <div class="mx-[5%] xl:mx-auto">
    <DishesHeader />
    <Table
      v-if="loaded"
      :labels="[t('dish.table.title'), t('dish.table.category'), t('dish.table.actions')]"
      :add-styles="'first:sticky first:left-0 first:bg-[#f4f7f9]'"
      :overflow-table="true"
    >
      <DishTableRow
        v-for="(dish, index) in filteredDishes"
        :key="index"
        :dish="
          // @ts-ignore
          dish as Dish
        "
        :index-in-list="index"
      />
    </Table>
    <LoadingSpinner :loaded="loaded" />
  </div>
</template>

<script setup lang="ts">
import DishTableRow from '@/components/dishes/DishTableRow.vue';
import DishesHeader from '@/components/dishes/DishesHeader.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import Table from '@/components/misc/Table.vue';
import { useCategories } from '@/stores/categoriesStore';
import { Dish, useDishes } from '@/stores/dishesStore';
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { fetchDishes, filteredDishes } = useDishes();
const { fetchCategories } = useCategories();
const loaded = ref(false);

onMounted(async () => {
  const progress = useProgress().start();
  await fetchDishes();
  await fetchCategories();
  loaded.value = true;
  progress.finish();
});
</script>
