<template>
  <router-link
    :to="`/participations/${weekId}/edit`"
    class="flex flex-row items-center gap-2"
  >
    <UserGroupIcon class="size-6" />
    <span>{{ t('printList.participations') }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { UserGroupIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  weekId: number;
}>();
</script>
