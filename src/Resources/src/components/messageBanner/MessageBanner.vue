<template>
  <transition
    enter-active-class="transition duration-150 ease-in"
    enter-from-class="transform -translate-y-20 opacity-0"
    enter-to-class="transform translate-y-0 opacity-100"
    leave-active-class="transition duration-150 ease-out"
    leave-from-class="transform tranlate-y-0 opacity-100"
    leave-to-class="transform -translate-y-20 opacity-0"
  >
    <div
      v-if="flashMessage[1] !== ''"
      class="absolute top-0 z-50 grid h-fit w-full max-w-screen-aoe grid-cols-[50px_minmax(0,1fr)] grid-rows-2 rounded-b-lg bg-white p-2 shadow-[0_0_15px_0_#7A8991]"
    >
      <ActionButton
        class="col-start-2 row-start-1 w-fit place-self-end"
        :btn-text="''"
        :action="Action.DELETE"
        @click="() => flashMessage[1] = ''"
      />
      <span
        class="col-start-2 row-start-2 self-center text-center"
      >
        {{ messageToShow }}
      </span>
      <div
        class="col-start-1 row-span-2 row-start-1 h-fit w-fit rounded-full border-2 border-solid border-white p-2"
        :class="flashMessage[0] === 'ERROR' ? 'bg-[#E02927]' : 'bg-[#51B848]'"
      >
        <CheckIcon
          v-if="flashMessage[0] !== 'ERROR'"
          class="h-8 w-8 text-white"
        />
        <div
          v-else
          class="grid h-8 w-8 place-content-center text-[24px] font-bold text-white"
        >
          !
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import useEventsBus from "@/tools/eventBus";
import { computed, ref } from "vue";
import ActionButton from "../misc/ActionButton.vue";
import { Action } from "@/enums/Actions";
import { CheckIcon } from "@heroicons/vue/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const { receive } = useEventsBus();
const flashMessage = ref<string[]>(['', '']);

receive<string[]>('flashmessage', (data) => {
  flashMessage.value = data;
});

const messageToShow = computed(() => {
  if (flashMessage.value[0] === '') {
    return t(`flashMessage.success.${flashMessage.value[1]}`);
  }
  return t(`flashMessage.error.${flashMessage.value[1].split(':')[0]}`);
});
</script>