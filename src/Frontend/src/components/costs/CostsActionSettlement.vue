<template>
  <Dialog
    :open="open"
    :initial-focus="initialFocus"
    class="relative z-50"
    @close="emit('confirm', false)"
  >
    <div class="fixed inset-0 flex items-center justify-center p-4">
      <DialogPanel class="relative overflow-hidden rounded-lg bg-white p-4 text-left shadow-xl sm:my-8 sm:p-6">
        <DialogTitle class="text-[18px]">
          {{ t('costs.settlement').replace('#name#', getFullNameByUser(username)) }}
        </DialogTitle>
        <div class="flex flex-row">
          <CreateButton
            ref="initialFocus"
            :managed="true"
            :btn-text="t('costs.settle')"
            class="flex-1 cursor-pointer"
            @click="emit('confirm', true)"
          />
          <CancelButton
            :btn-text="t('costs.cancel')"
            class="flex-1 cursor-pointer"
            @click="emit('confirm', false)"
          />
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';
import { useCosts } from '@/stores/costsStore';
import CancelButton from '../misc/CancelButton.vue';
import CreateButton from '@/components/misc/CreateButton.vue';
import { ref } from 'vue';

const { t } = useI18n();
const { getFullNameByUser } = useCosts();

defineProps<{
  open: boolean;
  username: string;
}>();

const emit = defineEmits(['confirm']);

const initialFocus = ref<HTMLElement | null>(null);
</script>
