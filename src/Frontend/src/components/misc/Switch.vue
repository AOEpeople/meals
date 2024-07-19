<template>
  <Switch
    v-model="enabled"
    :class="enabled ? 'bg-[#51B848]' : 'bg-white'"
    class="z-1 inline-flex h-[30px] w-[54px] shrink-0 cursor-pointer rounded-full border-2 border-transparent shadow-[0_0_2px_0px_rgba(0,0,0,0.5)] transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
  >
    <span class="sr-only">{{ sr }}</span>
    <span
      aria-hidden="true"
      :class="enabled ? 'translate-x-6' : 'translate-x-0'"
      class="pointer-events-none relative z-10 inline-block size-[26px] rounded-full bg-white shadow-[0_0_4px_0px_rgba(0,0,0,0.5)] ring-0 transition duration-200 ease-in-out"
    />
  </Switch>
</template>

<script setup lang="ts">
import { Switch } from '@headlessui/vue';
import { computed, ref, watch } from 'vue';

export interface Emits {
  (e: 'toggle', value: boolean): void;
}

const props = defineProps<{
  sr: string;
  initial: boolean;
}>();

const emits = defineEmits<Emits>();
const enabled = ref(props.initial);
const re = computed(() => props.initial);

watch(enabled, () => emits('toggle', enabled.value));
watch(re, () => (enabled.value = re.value));
</script>
