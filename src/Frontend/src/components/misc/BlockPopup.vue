<template>
  <TransitionRoot
    as="template"
    :show="isOpen"
  >
    <Dialog class="relative z-50">
      <div class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.2)] p-4">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <DialogPanel
            class="relative grid size-full place-content-center overflow-hidden p-4 text-left align-middle sm:my-8 sm:p-6"
          >
            <p class="max-w-[300px] text-center align-middle font-bold sm:max-w-sm">
              {{ t('popup.processing') }}
            </p>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';

defineProps<{
  isOpen: boolean;
}>();

const { t } = useI18n();
</script>
