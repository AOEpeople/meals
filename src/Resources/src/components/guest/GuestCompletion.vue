<template>
  <div :class="[result === 'resolve_success' ? 'bg-lime-800' : 'bg-rose', 'm-16 rounded']">
    <h2 class="text-white p-8 text-center">{{ t('guest.' + result) }}</h2>
  </div>
</template>

<script setup>
import {useI18n} from 'vue-i18n'

const { t } = useI18n()
defineProps(['result'])
</script>

<style scoped>

</style>