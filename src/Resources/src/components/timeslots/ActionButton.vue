<template>
  <button
    class="flex place-items-center"
    :class="[row ? 'flex-row' : 'flex-col']"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="aspect-square size-8"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="aspect-square size-10"
    />
    <p class="m-0 flex size-full place-items-center">
      {{ btnText }}
    </p>
  </button>
</template>

<script setup lang="ts">
import { Action } from '@/enums/Actions';
import { XIcon, PencilIcon } from '@heroicons/vue/outline';

withDefaults(
  defineProps<{
    btnText: string;
    action: Action;
    row?: boolean;
  }>(),
  {
    row: true
  }
);
</script>
