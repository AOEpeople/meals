<template>
  <footer class="bg-[rgb(225,232,238)]">
    <div class="max-w-[1170] mx-auto flex">
      <div class="mx-20 p-4 w-1/2 flex-nowrap justify-center">
        <img class="h-10" src="../../../../public/images/aoe-logo.svg" alt="AOE Logo" />
      </div>
      <div class="w-1/2">
        <a class="w-[50%] cursor-pointer hover:text-[#FF890E] justify-end self-center">
          <img class="max-w-[5%] w-4 mr-2" src="../../../../public/images/flag.svg" alt="flag" />
          {{ lang }} version
        </a>
      </div>
    </div>
    <div class="bg-[rgb(244,247,249)] drop-shadow-[0_1px_2px_rgba(0,0,0,0.1)] w-full py-4 px-4 text-center">
      <a> @ 2022 AOE. All rights reserved </a>
    </div>
  </footer>
</template>

<script setup>
let lang = 'German';

</script>