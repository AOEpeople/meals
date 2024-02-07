<template>
  <div id="link">
    <router-link to="/print/participations">
      <Icons
        icon="printer"
        class="mr-[10px] inline size-[16px] fill-primary"
      />
      <span class="text-[11px] font-bold leading-4 tracking-[1.5px] text-primary">{{ t('dashboard.print') }}</span>
    </router-link>
  </div>
</template>
<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icons from '@/components/misc/Icons.vue';

const { t } = useI18n();
</script>

<style scoped>
#link:hover a svg {
  @apply fill-secondary;
}

#link:hover a span {
  @apply text-secondary;
}
</style>
