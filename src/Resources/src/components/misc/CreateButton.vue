<template>
  <div
    :class="[ open ? 'translate-y-0.5' : 'shadow-btn btn-highlight-shadow' ]"
    class="rounded-btn bg-highlight text-btn drop-shadow-btn mx-2 mb-6 mt-4 flex h-9 items-center px-[34px] text-center font-bold leading-[10px] text-white transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
  >
    {{ btnText }}
  </div>
</template>

<script setup lang="ts">
defineProps<{
  btnText: string,
  open: boolean
}>();
</script>