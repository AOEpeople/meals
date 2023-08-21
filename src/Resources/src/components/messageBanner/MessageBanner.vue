<template>
  <div
    v-if="flashMessage[1] !== ''"
    class="absolute top-0 flex h-fit w-full max-w-screen-aoe flex-col rounded-b-lg p-2 shadow-[0_15px_35px_0_#5B788F21]"
    :class="flashMessage[0] === 'ERROR' ? 'bg-red' : 'bg-green'"
  >
    <ActionButton
      class="self-end"
      :btn-text="''"
      :action="Action.DELETE"
      @click="() => flashMessage[1] = ''"
    />
    <span
      class="self-center text-center"
    >
      {{ flashMessage[1] }}
    </span>
  </div>
</template>

<script setup lang="ts">
import useEventsBus from "@/tools/eventBus";
import { ref } from "vue";
import ActionButton from "../misc/ActionButton.vue";
import { Action } from "@/enums/Actions";

const { receive } = useEventsBus();
const flashMessage = ref<string[]>(['', '']);

receive<string[]>('flashmessage', (data) => {
  flashMessage.value = data;
});
</script>