<template>
  <input
    type="submit"
    :value="btnText ?? t('slot.save')"
    class="col-span-6 col-start-1 mx-auto mb-6 mt-4 flex h-9 cursor-pointer items-center rounded-btn bg-highlight px-[34px] text-btn font-bold leading-[10px] text-white drop-shadow-btn transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
    :class="[isMouseDown ? 'translate-y-0.5' : 'btn-highlight-shadow']"
    @mousedown="isMouseDown = true"
    @mouseup="isMouseDown = false"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  btnText?: string;
}>();

const isMouseDown = ref(false);
</script>
