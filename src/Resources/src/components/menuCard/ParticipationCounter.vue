<template>
  <div
    :class="[meal.limit > 9 ? 'min-w-[50px] min-[370px]:min-w-[65px]' : 'min-w-[40px] min-[370px]:min-w-[50px]', mealCSS]"
  >
    <div class="my-auto h-3 w-4 flex-auto min-[370px]:w-6 min-[370px]:flex-none">
      <Icons
        icon="person"
        box="0 0 12 12"
        class="mx-auto h-3 w-3 fill-white"
      />
    </div>

    <div class="my-auto h-3 flex-auto min-[370px]:mr-[5px]">
      <span class="block self-center align-middle text-[10px] font-bold leading-[12px] tracking-[1.5px] text-white min-[370px]:text-[12px]">
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
