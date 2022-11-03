<template>
  <div :class="
        [meal.limit > 9 ? 'w-[65px]' : 'w-[50px]', mealCSS]">
    <div class="table flex-auto">
      <div class="table-cell align-middle">
       <Icons icon="person" box="0 0 12 12" class="fill-white ml-[50%] -left-1/4 relative w-3 h-3"/>
      </div>
    </div>

    <div class="table flex-auto">
      <span class="table-cell align-middle text-white h-4 self-center leading-4 font-bold text-[12px] tracking-[1.5px]">
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
