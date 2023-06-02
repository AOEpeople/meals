<template>
  <input
    type="submit"
    :value="t('slot.save')"
    class="col-span-6 col-start-1 mx-auto mb-6 mt-4 flex h-9 cursor-pointer items-center rounded-btn bg-highlight px-[34px] text-btn font-bold leading-[10px] text-white drop-shadow-btn transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
    :class="activeBtnStyle"
    @mousedown="isMouseDown = true"
    @mouseup="isMouseDown = false"
  >
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const isMouseDown = ref(false);

const activeBtnStyle = computed(() => isMouseDown.value ? 'translate-y-0.5' : 'shadow-btn btn-highlight-shadow');
</script>