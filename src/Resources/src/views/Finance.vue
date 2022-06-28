<template>
  Finance
</template>

<script setup>
import {useProgress} from '@marcoschulte/vue3-progress'
const progress = useProgress().start()

setTimeout(function () {
    progress.finish()
}, 500)
</script>

<style scoped>

</style>