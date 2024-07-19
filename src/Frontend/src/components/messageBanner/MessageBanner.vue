<template>
  <transition
    enter-active-class="transition duration-200 ease-in"
    enter-from-class="transform -translate-y-20 opacity-0"
    enter-to-class="transform translate-y-0 opacity-100"
    leave-active-class="transition duration-200 ease-out"
    leave-from-class="transform tranlate-y-0 opacity-100"
    leave-to-class="transform -translate-y-20 opacity-0"
  >
    <div
      v-if="flashMessages.length > 0"
      class="absolute top-0 z-[9999] flex h-fit w-full max-w-screen-aoe flex-col rounded-b-lg bg-white p-2 shadow-[0_0_15px_0_#7A8991]"
    >
      <ActionButton
        class="w-fit place-self-end"
        data-cy="msgClose"
        :btn-text="''"
        :height-full="false"
        :action="Action.DELETE"
        @click="() => clearMessages()"
      />
      <MessageBannerData
        v-for="(flashMessage, index) in flashMessages"
        :key="`${flashMessage.message}_${String(index)}`"
        :message="flashMessage"
      />
    </div>
  </transition>
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import MessageBannerData from './MessageBannerData.vue';
import useFlashMessage from '@/services/useFlashMessage';

const { flashMessages, clearMessages } = useFlashMessage();
</script>
