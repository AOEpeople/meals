<template>
  <div class="mx-[5%] xl:mx-auto">
    <CategoriesHeader />
    <Table
      :labels="[t('category.table.title'), t('category.table.actions')]"
    >
      <tr
        v-for="(category, index) in CategoriesState.categories"
        :key="index"
        class="max-h-[62px] border-b-2 border-gray-200"
      >
        <td class="w-[80%]">
          <span class="text-[12px] xl:text-[18px]">
            {{ locale === 'en' ? category.titleEn : category.titleDe }}
          </span>
        </td>
        <td>
          <CategoriesActions
            :category="category"
            :index="index"
          />
        </td>
      </tr>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted } from 'vue';
import { useCategories } from '@/stores/categoriesStore';
import { useI18n } from 'vue-i18n';
import CategoriesHeader from '@/components/categories/CategoriesHeader.vue';
import Table from '@/components/misc/Table.vue';
import CategoriesActions from '@/components/categories/CategoriesActions.vue';

const { fetchCategories, CategoriesState } = useCategories();
const { t, locale } = useI18n();

onMounted(async () => {
  const progress = useProgress().start();
  await fetchCategories();
  progress.finish();
});
</script>