<template>
  <table 
    v-if="meal" 
    class="table-fixed border-2 border-solid border-black"
  >
    <thead>
      <tr>
        <th>{{ languageIsEnglish ? meal.title.en : meal.title.de }}</th>
      </tr>
    </thead>
    <tbody v-if="meal.variations.length > 0">
      <tr>
        <td
          v-for="(variation, index) in meal.variations"
          :key="index"
        >
          {{ getTitleForLocale(variation) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>


<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

defineProps({
  meal: null
});

const languageIsEnglish = computed(() => locale.value === 'en');

function getTitleForLocale(variation) {
  return languageIsEnglish.value ? variation.title.en : variation.title.de;
}

</script>