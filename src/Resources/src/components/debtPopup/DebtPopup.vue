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
            <p
              class="max-w-[300px] text-center align-middle font-bold sm:max-w-sm"
              data-cy="debtText"
            >
              {{
                t('debt.text').replace(
                  '#balance#',
                  new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(balance)
                )
              }}
            </p>
            <div class="flex flex-row">
              <CancelButton
                :btn-text="t('debt.ok')"
                class="flex-1 cursor-pointer"
                @click="isOpen = false"
              />
              <CreateButton
                :managed="true"
                :btn-text="t('debt.pay')"
                class="flex-1 cursor-pointer"
                @click="handlePayNow"
              />
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { userDataStore } from '@/stores/userDataStore';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CreateButton from '../misc/CreateButton.vue';
import { useI18n } from 'vue-i18n';
import CancelButton from '../misc/CancelButton.vue';

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();

const isOpen = ref(false);

const balance = computed(() => userDataStore.getState().balance);

onMounted(() => {
  setOpenOrClosed();
});

watch(
  () => route.path,
  () => setOpenOrClosed()
);

function handlePayNow() {
  isOpen.value = false;
  router.push({ name: 'Balance' });
}

function setOpenOrClosed() {
  const debtLimit = window.appData?.payment_notification_debt_limit as number;
  balance.value < debtLimit && route.path !== '/balance' ? (isOpen.value = true) : (isOpen.value = false);
}
</script>
