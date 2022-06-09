<template>
  <div class="flex flex-row-reverse gap-8 px-5 self-center items-center">
    <div class="contents basis-2/12 text-align-last">
      <input type="checkbox" :checked="props.data.active" class="w-[30px] h-[30px] border-[#ababab] rounded-md" />
      <div class="grid grid-cols-2 content-center leading-4 rounded-md w-[48px] h-[30px] bg-primary-4">
        <svg class="place-self-center" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M1 12C1 12 0 12 0 11C0 10 1 7 6 7C11 7 12 10 12 11C12 12 11 12 11 12H1ZM6 6C6.79565 6 7.55871 5.68393 8.12132 5.12132C8.68393 4.55871 9 3.79565 9 3C9 2.20435 8.68393 1.44129 8.12132 0.87868C7.55871 0.316071 6.79565 0 6 0C5.20435 0 4.44129 0.316071 3.87868 0.87868C3.31607 1.44129 3 2.20435 3 3C3 3.79565 3.31607 4.55871 3.87868 5.12132C4.44129 5.68393 5.20435 6 6 6Z" fill="white"/>
        </svg>
        <a class="text-white h-4 w-[15px] font-bold text-[11px] tracking-[1.5px]">{{ props.data.participations }}</a>
      </div>
    </div>
    <div class="basis-10/12 px-5 self-center">
      <a class="text-primary uppercase tracking-[1px] text-note font-bold">{{ props.data.name }}</a><br>
      <a :v-if="props.data.description !== ''" class="text-primary text-note font-light">{{ props.data.description }}</a>
    </div>
  </div>
</template>

<script setup>
const props = defineProps([
  'data',
]);

</script>

<style scoped>
.text-align-last {
  text-align-last: right;
}
</style>