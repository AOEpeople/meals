<template>
  <div
    :class="
      [meal.limit > 9 ? 'min-w-[65px]' : 'min-w-[50px]', mealCSS]"
  >
    <div class="my-auto h-3 w-6 flex-none">
      <Icons
        icon="person"
        box="0 0 12 12"
        class="mx-auto h-3 w-3 fill-white"
      />
    </div>

    <div class="my-auto mr-[5px] h-3 flex-auto">
      <span class="block self-center align-middle text-[12px] font-bold leading-[12px] tracking-[1.5px] text-white">
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
