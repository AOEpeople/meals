<template>
  <button
    class="z-[1] flex h-full place-items-center"
    :class="row ? 'flex-row' : 'flex-col'"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="z-[1] mr-1 aspect-square h-8 w-8"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="z-[1] aspect-square h-8 w-8"
    />
    <PlusIcon
      v-if="action === Action.CREATE"
      class="z-[1] mr-1 aspect-square h-8 w-8"
    />
    <p
      class="z-[1] m-0 flex h-full place-items-center text-left text-[10px] sm:text-[12px]"
      :class="[{ 'w-full': widthFull }, { 'hidden sm:flex': hideTextOnMobile }]"
    >
      {{ btnText }}
    </p>
  </button>
</template>

<script setup lang="ts">
import { Action } from '@/enums/Actions';
import { XIcon, PencilIcon, PlusIcon } from '@heroicons/vue/outline';

withDefaults(defineProps<{
  btnText: string,
  action: Action,
  row?: boolean,
  widthFull?: boolean,
  hideTextOnMobile?: boolean
}>(), {
  row: true,
  widthFull: true,
  hideTextOnMobile: false
});
</script>