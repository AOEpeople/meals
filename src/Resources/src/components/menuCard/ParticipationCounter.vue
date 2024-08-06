<template>
  <div
    class="mr-[5px] flex h-[30px] content-center rounded-md min-[380px]:mr-[11px] xl:h-[20px] print:bg-primary-2"
    :class="[limit > 9 ? 'min-w-[46px] min-[370px]:min-w-[65px]' : 'min-w-[40px] min-[370px]:min-w-[46px]', mealCSS]"
  >
    <div class="my-auto h-3 w-4 flex-auto min-[370px]:w-6 min-[370px]:flex-none">
      <Icons
        icon="person"
        box="0 0 12 12"
        class="mx-auto size-3 fill-white"
      />
    </div>
    <div class="my-auto h-3 flex-auto min-[370px]:mr-[5px]">
      <span
        class="block self-center text-center text-[10px] font-bold leading-[12px] tracking-[1.5px] text-white min-[370px]:text-[12px]"
      >
        <slot />
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icons from '@/components/misc/Icons.vue';

withDefaults(
  defineProps<{
    limit?: number;
    mealCSS: string;
  }>(),
  {
    limit: 0
  }
);

</script>
