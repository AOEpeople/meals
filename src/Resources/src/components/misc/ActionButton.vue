<template>
  <button
    class="z-[1] flex place-items-center rounded-lg px-[2px] hover:shadow-[0px_2px_8px_-1px_rgba(0,0,0,0.2)]"
    :class="[row ? 'flex-row' : 'flex-col', heightFull ? 'h-full' : 'h-auto']"
  >
    <PencilIcon
      v-if="action === Action.EDIT"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
    />
    <XIcon
      v-if="action === Action.DELETE"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
    />
    <PlusIcon
      v-if="action === Action.CREATE"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
    />
    <ScaleIcon
      v-if="action === Action.BALANCE"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
    />
    <EyeOffIcon
      v-if="action === Action.HIDE"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
    />
    <DownloadIcon
      v-if="action === Action.DOWNLOAD"
      class="z-[1] aspect-square size-8"
      :class="{ 'mr-1': btnText !== '' }"
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
import { XIcon, PencilIcon, PlusIcon, DownloadIcon } from '@heroicons/vue/outline';
import { EyeOffIcon, ScaleIcon } from '@heroicons/vue/solid';

withDefaults(
  defineProps<{
    btnText: string;
    action: Action;
    row?: boolean;
    widthFull?: boolean;
    heightFull?: boolean;
    hideTextOnMobile?: boolean;
  }>(),
  {
    row: true,
    widthFull: true,
    heightFull: true,
    hideTextOnMobile: false
  }
);
</script>
