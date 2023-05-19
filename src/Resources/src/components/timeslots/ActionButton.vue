<template>
  <button
    class="z-[1] flex place-items-center"
    :class="[row ? 'flex-row' : 'flex-col']"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="z-[1] aspect-square h-8 w-8"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="z-[1] aspect-square h-10 w-10"
    />
    <p class="z-[1] m-0 flex h-full w-full place-items-center">
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
  row?: boolean
}>(), {
  row: true
});
</script>