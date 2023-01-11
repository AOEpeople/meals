<template>
  <div
    :class="
      [meal.limit > 9 ? 'w-[65px]' : 'w-[50px]', mealCSS]"
  >
    <div class="table flex-auto">
      <div class="table-cell align-middle">
        <Icons
          icon="person"
          box="0 0 12 12"
          class="relative -left-1/4 ml-[50%] h-3 w-3 fill-white"
        />
      </div>
    </div>

    <div class="table flex-auto">
      <span class="table-cell h-4 self-center align-middle text-[12px] font-bold leading-4 tracking-[1.5px] text-white">
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
