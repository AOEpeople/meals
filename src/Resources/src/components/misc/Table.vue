<template>
  <div class="mx-auto flex max-w-screen-aoe flex-col">
    <div class="inline-block min-w-full py-2">
      <table class="min-w-full max-w-fit table-fixed border-spacing-0">
        <thead>
          <tr>
            <th
              v-for="label in labels"
              :key="label"
              scope="col"
              class="text-[11px] font-bold uppercase leading-4 tracking-[1.5px] last:text-right"
              :class="style"
            >
              {{ label }}
            </th>
          </tr>
        </thead>
        <tbody class="text-[18px] font-light leading-6">
          <slot />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  labels: string[],
  headerTextPosition?: string
}>(), {
  headerTextPosition: 'left'
});

const style = computed(() => {
  switch(props.headerTextPosition) {
    case 'left':
      return 'text-left';
    case 'lmr':
      return 'first:text-left last:text-right text-center';
    case 'lfr':
      return 'first:text-left text-right';
    default:
      return 'text-left';
  }

});
</script>