<template>
  <TransitionRoot
    as="template"
    :show="isOpen"
  >
    <Dialog class="relative z-50">
      <div class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.1)] p-4">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <DialogPanel class="relative overflow-hidden rounded-lg bg-white p-4 text-left shadow-xl sm:my-8 sm:p-6">
            <p class="max-w-[300px] text-center align-middle font-bold sm:max-w-sm">
              {{ t('flashMessage.success.events.lunchroulette') }}
            </p>
            <div class="mt-4 flex justify-center">
              <CancelButton
                :btn-text="t('debt.ok')"
                class="flex-1 cursor-pointer"
                @click="closePopup"
              />
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import CancelButton from '../misc/CancelButton.vue';
import { useI18n } from 'vue-i18n';

const isOpen = ref(false);
const emit = defineEmits(['close']);
const { t } = useI18n();

const openPopup = () => {
  isOpen.value = true;
};

const closePopup = () => {
  isOpen.value = false;
  emit('close');
};

defineExpose({ openPopup });
</script>
