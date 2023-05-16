<template>
  <div class="grid grid-cols-3">
    <button>edit</button>
    <button>delete</button>
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

const { changeDisabledState } = useTimeSlots();

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