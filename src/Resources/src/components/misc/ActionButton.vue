<template>
  <button
    class="z-[1] flex h-full place-items-center rounded-lg px-[2px] hover:shadow-[0px_2px_8px_-1px_rgba(0,0,0,0.2)]"
    :class="row ? 'flex-row' : 'flex-col'"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="z-[1] aspect-square h-8 w-8"
      :class="{'mr-1': btnText !== ''}"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="z-[1] aspect-square h-8 w-8"
      :class="{'mr-1': btnText !== ''}"
    />
    <PlusIcon
      v-if="action === Action.CREATE"
      class="z-[1] aspect-square h-8 w-8"
      :class="{'mr-1': btnText !== ''}"
    />
    <ScaleIcon
      v-if="action === Action.BALANCE"
      class="z-[1] aspect-square h-8 w-8"
      :class="{'mr-1': btnText !== ''}"
    />
    <EyeOffIcon
      v-if="action === Action.HIDE"
      class="z-[1] aspect-square h-8 w-8"
      :class="{'mr-1': btnText !== ''}"
    />
    <p
      v-if="btnText !== ''"
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
import { EyeOffIcon, ScaleIcon } from '@heroicons/vue/solid';

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