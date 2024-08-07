<template>
  <div class="group relative my-auto grid content-center">
    <img
      v-if="diet === Diet.VEGETARIAN"
      class="object-cover"
      :class="maxHeight"
      src="../../../images/vegetarian.png"
      alt="vegetarian"
      data-cy="vegetarian-icon"
    />
    <img
      v-else-if="diet === Diet.VEGAN"
      class="object-cover"
      :class="maxHeight"
      src="../../../images/vegan.png"
      alt="vegan"
      data-cy="vegan-icon"
    />
    <span
      v-if="tooltipActive && diet !== Diet.MEAT"
      class="pointer-events-none absolute bottom-[125%] left-1/2 ml-[-60px] w-[120px] rounded-md bg-[#585858] px-2 py-1 text-center text-[12px] font-normal text-white opacity-0 shadow transition-opacity after:absolute after:left-1/2 after:top-full after:ml-[-5px] after:border-[5px] after:border-solid after:border-triangle group-hover:opacity-90"
    >
      {{ t(`dish.diet.${diet.toString()}`) }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { Diet } from '@/enums/Diet';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

withDefaults(
  defineProps<{
    diet: Diet;
    tooltipActive?: boolean;
    maxHeight?: string;
  }>(),
  {
    tooltipActive: true,
    maxHeight: ''
  }
);
</script>
