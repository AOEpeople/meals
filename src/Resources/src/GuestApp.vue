<template>
  <NavBar guest="true"/>
  <Content />
  <Footer />
</template>

<script setup>
import { useRoute } from 'vue-router'
import Content from "@/components/Content.vue";
import Footer from "@/components/Footer.vue";
import NavBar from "@/components/NavBar.vue";

const route = useRoute()
const hash = route.params.hash

</script>
<style>

</style>