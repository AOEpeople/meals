<template>
  <div
    class="mx-auto flex flex-col"
    :class="[print === true ? 'w-[700px]' : 'max-w-screen-aoe', { 'overflow-x-auto': overflowTable }]"
  >
    <div class="inline-block min-w-full py-2">
      <table class="min-w-full max-w-fit table-fixed border-spacing-0">
        <thead>
          <tr>
            <th
              v-for="label in labels"
              :key="label"
              scope="col"
              class="px-1 text-[11px] font-bold uppercase leading-4 tracking-[1.5px] last:text-right"
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

const props = withDefaults(
  defineProps<{
    labels: string[];
    headerTextPosition?: string;
    print?: boolean;
    addStyles?: string;
    overflowTable?: boolean;
  }>(),
  {
    headerTextPosition: 'left',
    print: false,
    addStyles: '',
    overflowTable: false
  }
);

const style = computed(() => {
  let returnStyle = props.addStyles;
  switch (props.headerTextPosition) {
    case 'left':
      returnStyle += ' text-left';
      break;
    case 'lmr':
      returnStyle += ' first:text-left last:text-right text-center';
      break;
    case 'lfr':
      returnStyle += ' first:text-left text-right';
      break;
    default:
      returnStyle += ' text-left';
      break;
  }
  return returnStyle.trimStart();
});
</script>
