<template>
  <div>
    <span class="whitespace-nowrap text-note font-light text-primary"> {{ localePrice }} </span>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const props = defineProps(['price'])
const localePrice = computed(() => locale.value.substring(0, 2) === 'en' ? '€ ' + props.price.toFixed(2) : props.price.toFixed(2).replace(/\./g, ',') +  '€')
</script>
