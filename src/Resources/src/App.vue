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

balanceStore.updateAmount(parseFloat(sessionStorage.getItem('balance')))
</script>
