<template>
  <div class="flex min-h-screen flex-col print:h-[29.7cm] print:w-[21cm]">
    <NavBar
      id="navibar"
      ref="navibar"
    />
    <div class="absolute z-[4]">
      <vue3-progress-bar />
    </div>
    <DebtPopup />
    <SessionCheckerPopup />
    <Content class="relative z-[2] grow" />
    <Footer
      v-if="!showParticipations"
      class="relative z-[1] mt-auto"
    />
  </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import Content from '@/components/Content.vue';
import { useRoute } from 'vue-router';
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import { useComponentHeights } from '@/services/useComponentHeights';
import DebtPopup from './components/debtPopup/DebtPopup.vue';
import SessionCheckerPopup from '@/components/misc/SessionCheckerPopup.vue';

const route = useRoute();
const { setNavBarHeight, windowWidth } = useComponentHeights();

const navibar = ref(null);

const showParticipations = computed(() => {
  return route.path === '/show/participations';
});

watch(windowWidth, () => {
  if (navibar.value !== null && navibar.value !== undefined) {
    setNavBarHeight(navibar.value.$el.offsetHeight, 'navibar');
  }
});

onMounted(() => {
  if (navibar.value !== null && navibar.value !== undefined) {
    setNavBarHeight(navibar.value.$el.offsetHeight, 'navibar');
  }
});

onUpdated(() => {
  if (navibar.value !== null && navibar.value !== undefined) {
    setNavBarHeight(navibar.value.$el.offsetHeight, 'navibar');
  }
});
</script>

<style>
.btn-disabled {
  @apply mx-2 mb-6 mt-4 h-9 items-center rounded-btn px-[34px] text-center text-btn font-medium shadow-btn drop-shadow-btn;
  @apply bg-grey text-white shadow-light-grey;
}
.aoe-shadow {
  box-shadow:
    0 4px 0 hsla(0, 0%, 100%, 0.46),
    0 15px 35px rgba(216, 225, 233, 0.8);
}

.btn-highlight-shadow {
  box-shadow:
    0 6px 8px rgba(0, 0, 0, 0.25),
    0 3px 0 #ff890e;
}
</style>
