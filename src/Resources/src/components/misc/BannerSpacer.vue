<template>
  <div
    class="justify-content grid w-full grid-cols-[minmax(0,1fr)_minmax(0,3fr)_minmax(0,1fr)] grid-rows-1 items-center py-2"
  >
    <div class="col-span-1 col-start-1 h-[2px] bg-[#e5e7eb]" />
    <span
      class="col-span-1 col-start-2 flex h-full flex-row items-center justify-around gap-2 rounded-full border-2 border-solid px-4 py-1"
    >
      <slot />
    </span>
    <div class="col-span-1 col-start-3 h-[2px] bg-[#e5e7eb]" />
  </div>
</template>

<script setup lang="ts"></script>
