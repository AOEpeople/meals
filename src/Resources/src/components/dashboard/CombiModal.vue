<template>
  <TransitionRoot
    as="template"
    :show="open"
  >
    <Dialog
      as="div"
      class="relative z-10"
      @close="resolveModal('cancel')"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6"
            >
              <div>
                <div class="mt-3 sm:mt-5">
                  <DialogTitle
                    as="h2"
                    class="text-primary"
                  >
                    {{ t('combiModal.title') }}
                  </DialogTitle>
                  <div
                    v-for="key in keys"
                    :key="key"
                    class="mt-2 grid"
                  >
                    <CombiButtonGroup
                      :key="key"
                      :weekID="weekID"
                      :dayID="dayID"
                      :mealID="key"
                      :meal="meals[key]"
                      @addEntry="addEntry"
                      @removeEntry="removeEntry"
                    />
                  </div>
                </div>
              </div>
              <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                <button
                  type="button"
                  :class="[bookingDisabled ? 'btn-disabled' : 'btn-primary', 'inline-flex w-full justify-center']"
                  :disabled="bookingDisabled"
                  @click="resolveModal('book')"
                >
                  {{ t('combiModal.submit') }}
                </button>
                <button
                  ref="cancelButtonRef"
                  type="button"
                  class="btn-tertiary inline-flex w-full justify-center"
                  @click="resolveModal('cancel')"
                >
                  {{ t('combiModal.cancel') }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import CombiButtonGroup from '@/components/dashboard/CombiButtonGroup.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Meal } from '@/api/getDashboardData';
import { Dictionary } from 'types/types';

const props = defineProps<{
  open: boolean;
  weekID?: number | string;
  dayID?: number | string;
  meals: Dictionary<Meal>;
}>();

const { t } = useI18n();
const emit = defineEmits(['closeCombiModal']);
const meals = props.meals ? props.meals : dashboardStore.getMeals(props.weekID, props.dayID);
let keys = Object.keys(meals).filter((mealID) => meals[mealID].dishSlug !== 'combined-dish');
const slugs = ref<string[]>([]);
const bookingDisabled = computed(() => slugs.value.length < 2);

function resolveModal(mode: string) {
  if (mode === 'cancel') {
    slugs.value = [];
    emit('closeCombiModal');
  }
  if (mode === 'book') {
    emit('closeCombiModal', slugs.value);
    slugs.value = [];
  }
}

function removeEntry(slug: string) {
  slugs.value = slugs.value.filter((entry) => entry !== slug);
}

function addEntry(slug: string) {
  slugs.value.push(slug);
}
</script>
