<template>
  <TransitionRoot
    as="template"
    :show="isOpen"
  >
    <Dialog class="relative z-50">
      <div class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.1)]">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <DialogPanel class="relative overflow-hidden rounded-lg bg-white text-left shadow-xl">
            <slot />
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';

defineProps<{
  isOpen: boolean;
}>();
</script>
