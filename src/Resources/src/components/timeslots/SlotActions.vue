<template>
  <div class="z-1 flex flex-row content-center items-center justify-end justify-items-end gap-4">
    <ActionButton
      :action="Action.EDIT"
      :btn-text="t('button.edit')"
      :row="true"
    />
    <ActionButton
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :row="true"
    />
    <Switch
      :sr="'Enable TimeSlot'"
      :initial="initial"
      @toggle="// @ts-ignore-error ts thinks toggle has any type but is typed in switch
        (state: boolean) => enabled = state"
    />
  </div>
</template>

<script setup lang="ts">
import Switch from "@/components/misc/Switch.vue"
import { ref, watch } from "vue"
import { useTimeSlots, TimeSlot } from "@/stores/timeSlotStore";
import ActionButton from "./ActionButton.vue";
import { useI18n } from "vue-i18n";
import { Action } from '@/enums/Actions';

const { changeDisabledState } = useTimeSlots();
const { t } = useI18n();

const props = defineProps<{
  timeSlot: TimeSlot,
  timeSlotID: number
}>();

const initial = ref(props.timeSlot.enabled);
const enabled = ref(initial.value)

watch(enabled, async () => {
  await changeDisabledState(props.timeSlotID, enabled.value);
});
</script>