<template>
  <NavBar />
  <vue3-progress-bar></vue3-progress-bar>
  <Content />
  <Footer />
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
import Footer from '@/components/Footer.vue'
import Content from '@/components/Content.vue'

import { balanceStore } from '@/store/balanceStore'

balanceStore.fillStore()
</script>
<style>
.btn-disabled {
  @apply shadow-btn font-medium drop-shadow-btn text-btn rounded-btn mx-2 mt-4 mb-6 px-[34px] h-9 text-center items-center;
  @apply bg-grey text-white shadow-light-grey;
}
</style>