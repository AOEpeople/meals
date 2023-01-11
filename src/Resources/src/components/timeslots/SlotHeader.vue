<template>
  <div class="items-center text-center xl:my-[42px] xl:grid xl:grid-cols-2">
    <div class="xl:justify-self-start">
      <h2 class="m-0">
        {{ t('slots.header') }}
      </h2>
    </div>
    <div class="xl:justify-self-end">
      <Popover>
        <template #button="{ open }">
          <button class="btn-secondary">
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
