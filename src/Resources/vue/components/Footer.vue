<template>
  <footer class="bg-[rgb(225,232,238)]">
    <div class="grid mx-auto max-w-screen-aoe xl:grid-cols-2">
      <div class="items-center py-4">
        <img class="mx-auto h-10 xl:mx-0" src="../../../../public/images/aoe-logo.svg" alt="AOE Logo" />
      </div>
      <div class="hidden self-center text-right xl:inline-block">
        <img class="inline-block mr-1 w-5" src="../../../../public/images/flag.svg" alt="flag" />
        <a class="self-center cursor-pointer hover:text-highlight">
          {{ lang }} version
        </a>
      </div>
    </div>
    <div class="bg-[rgb(244,247,249)] w-full py-4 px-4 text-center">
      <a> @ 2022 AOE. All rights reserved </a>
    </div>
  </footer>
</template>

<script setup>
let lang = 'German';

</script>