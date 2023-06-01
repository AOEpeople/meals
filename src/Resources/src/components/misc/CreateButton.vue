<template>
  <div
    :class="[ open ? 'translate-y-0.5' : 'btn-highlight-shadow shadow-btn' ]"
    class="mx-2 mb-6 mt-4 flex h-9 items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold leading-[10px] text-white drop-shadow-btn transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
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