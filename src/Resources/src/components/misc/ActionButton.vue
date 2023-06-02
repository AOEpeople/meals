<template>
  <button
    class="z-[1] flex place-items-center"
    :class="[row ? 'flex-row' : 'flex-col']"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="z-[1] mr-1 aspect-square h-8 w-8"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="z-[1] aspect-square h-10 w-10"
      :class="widthFull ? 'w-8' : 'w-8'"
    />
    <p
      class="z-[1] m-0 flex h-full place-items-center text-[10px] sm:text-[12px]"
      :class="widthFull ? 'w-full' : ''"
    >
      {{ btnText }}
    </p>
  </button>
</template>

<script setup lang="ts">
import { Action } from '@/enums/Actions';
import { XIcon, PencilIcon } from '@heroicons/vue/outline';

withDefaults(defineProps<{
  btnText: string,
  action: Action,
  row?: boolean,
  widthFull?: boolean
}>(), {
  row: true,
  widthFull: true
});
</script>