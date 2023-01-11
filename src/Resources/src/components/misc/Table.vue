<template>
  <div class="mx-auto flex max-w-screen-aoe flex-col">
    <div class="inline-block min-w-full py-2">
      <table class="border-spacing-none min-w-full max-w-fit table-fixed">
        <thead>
          <tr>
            <th
              v-for="label in localeLabels"
              :key="label"
              scope="col"
              class="text-left text-[11px] font-bold uppercase leading-4 tracking-[1.5px]"
            >
              {{ label }}
            </th>
          </tr>
        </thead>
        <tbody class="text-[18px] font-light leading-6">
          <slot />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import {computed} from "vue";
const { locale } = useI18n();
const props = defineProps([
    'labels',
])

let localeLabels = computed(() => locale.value === 'en' ? props.labels.en : props.labels.de);
</script>

<style scoped>
.border-spacing-none {
  border-spacing: 0
}

th:last-child {
  text-align: right;
}


</style>