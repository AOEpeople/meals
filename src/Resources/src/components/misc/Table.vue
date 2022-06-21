<template>
  <div class="flex flex-col max-w-screen-aoe mx-auto">
    <div class="inline-block min-w-full py-2">
      <table class="min-w-full border-spacing-none table-fixed max-w-fit">
        <thead>
          <tr>
            <th scope="col"
                class="text-left text-[11px] leading-4 font-bold tracking-[1.5px] uppercase"
                v-for="label in localeLabels"
                :key="label"
            >
              {{ label }}
            </th>
          </tr>
        </thead>
        <tbody class="text-[18px] leading-6 font-light">
          <slot></slot>
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

<style>
.border-spacing-none {
  border-spacing: 0
}

th:last-child {
  text-align: right;
}


</style>