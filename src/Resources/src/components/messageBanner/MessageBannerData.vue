<template>
  <div class="grid w-full grid-cols-[50px_minmax(0,1fr)]">
    <div
      class="col-start-1 m-auto size-fit rounded-full border-2 border-solid border-white p-2"
      :class="message.type !== FlashMessageType.INFO ? 'bg-[#E02927]' : 'bg-[#51B848]'"
    >
      <CheckIcon
        v-if="message.type === FlashMessageType.INFO"
        class="size-8 text-white"
      />
      <div
        v-else
        class="grid size-8 place-content-center text-[24px] font-bold text-white"
      >
        !
      </div>
    </div>
    <p class="col-start-2 m-auto self-center text-center">
      {{ messageToShow }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { type FlashMessage } from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { CheckIcon } from '@heroicons/vue/outline';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  message: FlashMessage;
}>();

const { t } = useI18n();

const messageToShow = computed(() => {
  if (props.message.type === FlashMessageType.INFO) {
    return t(`flashMessage.success.${props.message.message}`);
  } else if (props.message.type === FlashMessageType.ERROR) {
    return t(`flashMessage.error.${props.message.message}`);
  }
  return props.message.message;
});
</script>
