<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end sm:gap-4">
    <Popover
      :translate-x-max="'-30%'"
      :translate-x-min="'-30%'"
    >
      <template #button="{ open }">
        <ActionButton
          :action="Action.EDIT"
          :btn-text="t('button.edit')"
          class="relative z-0 h-[40px]"
        />
      </template>
      <template #panel="{ close }">
        <SlotCreationPanel
          :id="timeSlotId"
          :edit="true"
          :submit="t('slot.save')"
          :header="t('slot.editSlot')"
          :title="timeSlot.title"
          :limit="String(timeSlot.limit)"
          :order="String(timeSlot.order)"
          :slug="String(timeSlot.slug)"
          @closePanel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :row="true"
      :width-full="false"
      @click="timeSlot.slug && deleteSlotWithSlug(timeSlot.slug)"
    />
    <Switch
      :sr="'Enable TimeSlot'"
      :initial="initial"
      @toggle="(value) => setEnabled(value)"
    />
  </div>
</template>

<script setup lang="ts">
import Switch from '@/components/misc/Switch.vue';
import { ref, watch } from 'vue';
import { useTimeSlots, type TimeSlot } from '@/stores/timeSlotStore';
import ActionButton from '@/components/misc/ActionButton.vue';
import { useI18n } from 'vue-i18n';
import { Action } from '@/enums/Actions';
import Popover from '../misc/Popover.vue';
import SlotCreationPanel from './SlotCreationPanel.vue';

const { changeDisabledState, deleteSlotWithSlug } = useTimeSlots();
const { t } = useI18n();

const props = defineProps<{
  timeSlot: TimeSlot;
  timeSlotId: number;
}>();

const initial = ref(props.timeSlot.enabled);
const enabled = ref(initial.value);

watch(enabled, async () => {
  await changeDisabledState(props.timeSlotId, enabled.value);
});

function setEnabled(state: boolean) {
  enabled.value = state;
}
</script>
