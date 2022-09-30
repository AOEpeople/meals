<template>
<span>{{ url }}</span>
</template>

<script setup>
import {useGuestLink} from "@/hooks/getGuestLink";

const props = defineProps(['dayID'])
let url = ''

const { link, error } = await useGuestLink(props.dayID)
if (error.value === false) {
  url = link.value.url
}

</script>

<style scoped>

</style>