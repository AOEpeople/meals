<template>
  <div :class="
        [meal.limit > 9 ? 'w-[65px]' : 'w-[50px]', mealCSS]">
    <div class="flex-auto">
      <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 my-[7px] mx-1"/>
    </div>
    <div class="flex-auto">
      <span class="text-white h-4 self-center leading-4 font-bold text-[12px] my-0.5 mr-[7px] tracking-[1.5px]">
          {{ participations }}
      </span>
    </div>
  </div>
</template>
<script setup>
import Icons from '@/components/misc/Icons.vue'
import { computed } from 'vue'

const props = defineProps(['meal', 'mealCSS'])

const participations = computed(() => props.meal.limit > 0 ? props.meal.participations + '/' + props.meal.limit : props.meal.participations)

</script>
