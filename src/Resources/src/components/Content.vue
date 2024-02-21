<template>
  <main>
    <div
      class="mx-auto"
      :class="[isShowParticipations ? 'max-w-full' : 'mt-10 max-w-screen-aoe print:mt-0']"
    >
      <MessageBanner v-if="isShowParticipations !== true" />
      <Suspense>
        <template #default>
          <router-view v-slot="{ Component }">
            <component :is="Component" />
          </router-view>
        </template>
        <template #fallback>
          <LoadingSpinner :loaded="false" />
        </template>
      </Suspense>
    </div>
  </main>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MessageBanner from '@/components/messageBanner/MessageBanner.vue';
import LoadingSpinner from './misc/LoadingSpinner.vue';

const route = useRoute();

const isShowParticipations = computed(() => route.path === '/show/participations');
</script>
