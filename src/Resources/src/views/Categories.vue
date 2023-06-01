<template>
  <div>
    <CategoriesHeader />
    <Table
      :labels="[t('category.table.title'), t('category.table.actions')]"
    >
      <tr
        v-for="(category, id) in CategoriesState.categories"
        :key="id"
      >
        <td class="w-[80%]">
          <span>
            {{ locale === 'en' ? category.titleEn : category.titleDe }}
          </span>
        </td>
        <td>
          <CategoriesActions />
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