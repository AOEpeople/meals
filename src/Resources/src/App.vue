<template>
  <NavBar />
  <div class="absolute z-[4]">
    <vue3-progress-bar />
  </div>
  <Content class="relative z-[2]" />
  <Footer class="relative z-[1]" />
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
import Footer from '@/components/Footer.vue'
import Content from '@/components/Content.vue'
</script>

<style>
.btn-disabled {
  @apply shadow-btn font-medium drop-shadow-btn text-btn rounded-btn mx-2 mt-4 mb-6 px-[34px] h-9 text-center items-center;
  @apply text-white bg-grey shadow-light-grey;
}
.aoe-shadow {
  box-shadow: 0 4px 0 hsla(0,0%,100%,.46),0 15px 35px rgba(216,225,233,.8);
}

.btn-highlight-shadow {
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.25), 0 3px 0 #FF890E;
}
</style>
