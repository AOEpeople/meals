<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-10" @close="emit('closeCombiModal')">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:p-6">
              <div>
                <div class="mt-3 sm:mt-5">
                  <DialogTitle as="h2" class="text-primary">Choose a combination for your combined dish </DialogTitle>
                  <div v-for="key in keys"
                       class="grid mt-2"
                       :key="key"
                  >
                    <CombiButtonGroup
                        :weekID="weekID"
                        :dayID="dayID"
                        :mealID="key"
                        :key="key"
                        @addEntry="addEntry"
                        @removeEntry="removeEntry"/>
                  </div>
                </div>
              </div>
              <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                    type="button"
                    :class="[bookingDisabled ? 'btn-disabled' : 'btn-primary', 'w-full inline-flex justify-center']"
                    @click="resolveModal('book')"
                    :disabled="bookingDisabled"
                >
                  Book
                </button>
                <button
                    type="button"
                    class="w-full inline-flex justify-center btn-tertiary"
                    @click="resolveModal('cancel')"
                    ref="cancelButtonRef"
                >
                  Cancel
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import CombiButtonGroup from "@/components/dashboard/CombiButtonGroup.vue";
import {dashboardStore} from "@/store/dashboardStore";
import {computed, ref} from "vue";

const props = defineProps([
    'open',
    'weekID',
    'dayID',
])
const emit = defineEmits(['closeCombiModal'])

let meals = dashboardStore.getMeals(props.weekID, props.dayID)
let keys = Object.keys(meals).filter(mealID => meals[mealID].dishSlug !== 'combined-dish')
const slugs = ref([])
const bookingDisabled = computed(() => slugs.value.length < 2)

function resolveModal(mode) {
  if(mode === 'cancel') {
    slugs.value = []
    emit("closeCombiModal")
  }
  if(mode === 'book') {
    emit("closeCombiModal", slugs.value)
    slugs.value = []
  }
}

function removeEntry(slug) {
  slugs.value = slugs.value.filter(entry => entry !== slug)
}

function addEntry(slug) {
  slugs.value.push(slug)
  console.log(slugs.value.length)
}

</script>