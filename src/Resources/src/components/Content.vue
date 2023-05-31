<template>
  <main>
    <div
      class="mx-auto"
      :class="[isShowParticipations ? 'max-w-full' : 'mt-10 max-w-screen-aoe']"
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

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const isShowParticipations = computed(() => route.path === '/show/participations');
</script>