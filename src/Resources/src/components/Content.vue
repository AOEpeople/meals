<template>
  <main>
    <div
      class="mx-auto mt-10"
      :class="[isShowParticipations ? 'max-w-full' : 'max-w-screen-aoe']"
    >
      <Suspense>
        <template #default>
          <router-view v-slot="{ Component }">
            <component :is="Component" />
          </router-view>
        </template>
        <template #fallback>
          <div> loading </div>
        </template>
      </Suspense>
    </div>
  </main>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const isShowParticipations = computed(() => route.path === '/show/participations');
</script>