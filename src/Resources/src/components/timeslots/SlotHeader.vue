<template>
  <div class="items-center text-center xl:my-[42px] xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h2 class="m-0">
        {{ t('slot.header') }}
      </h2>
    </div>
    <div class="xl:justify-self-end">
      <Popover>
        <template #button="{ open }">
          <button class="hover:bg-highlight-2 btn-highlight-shadow rounded-btn bg-highlight text-btn shadow-btn drop-shadow-btn active:shadow-btn-active mx-2 mb-6 mt-4 h-9 items-center px-[34px] text-center font-bold text-white transition-all duration-300 ease-out active:translate-y-0.5">
            <span class="align-middle leading-[10px]">+ {{ t('slot.create') }}</span>
          </button>
        </template>
        <template #panel="{ close }">
          <SlotCreationPanel @closePanel="close()" />
        </template>
      </Popover>
    </div>
  </div>
</template>

<script setup>
import Popover from "@/components/misc/Popover.vue"
import SlotCreationPanel from "@/components/timeslots/SlotCreationPanel.vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
</script>

<style scoped>

</style>
