<template>
  <div class="mx-[5%] xl:mx-auto">
    <DishesHeader />
    <Table
      v-if="loaded"
      :labels="[t('dish.table.title'), t('dish.table.category'), t('dish.table.actions')]"
    >
      <DishTableRow
        v-for="(dish, index) in filteredDishes"
        :key="index"
        :dish="// @ts-ignore
          (dish as Dish)"
        :index-in-list="index"
      />
    </Table>
    <LoadingSpinner
      :loaded="loaded"
    />
  </div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import Table from '@/components/misc/Table.vue';
import { useI18n } from 'vue-i18n';
import { onMounted, ref } from 'vue';
import { useDishes } from '@/stores/dishesStore';
import DishesHeader from '@/components/dishes/DishesHeader.vue';
import { useCategories } from '@/stores/categoriesStore';
import DishTableRow from '@/components/dishes/DishTableRow.vue';
import { Dish } from '@/stores/dishesStore';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';

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