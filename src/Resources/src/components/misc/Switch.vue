<template>
  <div>
    <Switch
      v-model="enabled"
      :class="enabled ? 'bg-teal-900' : 'bg-teal-700'"
      class="relative inline-flex h-[38px] w-[74px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
    >
      <span class="sr-only">{{ sr }}</span>
      <span
        aria-hidden="true"
        :class="enabled ? 'translate-x-9' : 'translate-x-0'"
        class="pointer-events-none inline-block h-[34px] w-[34px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
      />
    </Switch>
  </div>
</template>

<script setup>
import { Switch } from '@headlessui/vue'
import {computed, ref, watch} from "vue";

const props = defineProps(['sr', 'initial'])
const emits = defineEmits(['toggle'])
const enabled = ref(props.initial)
const re = computed(() => props.initial)

watch(enabled, () => emits('toggle', enabled.value))
watch(re, () => enabled.value = re.value)
</script>