<template>
  <NavBar />
  <vue3-progress-bar />
  <Content class="z-[2] relative" />
  <Footer class="z-[1] relative" />
</template>

<script setup>
import NavBar from '@/components/NavBar.vue'
import Footer from '@/components/Footer.vue'
import Content from '@/components/Content.vue'

import { balanceStore } from '@/store/balanceStore'

const isAuthenticated = sessionStorage.getItem('auth') === 'granted'
if (isAuthenticated) {
  balanceStore.fillStore()
}
</script>

<style>
.btn-disabled {
  @apply shadow-btn font-medium drop-shadow-btn text-btn rounded-btn mx-2 mt-4 mb-6 px-[34px] h-9 text-center items-center;
  @apply text-white bg-grey shadow-light-grey;
}
</style>
