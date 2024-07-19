<template>
  <div>
    <router-link to="/show/participations">
      <span class="text-[11px] font-bold leading-4 tracking-[1.5px] text-primary">{{ t('dashboard.show') }}</span>
    </router-link>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
