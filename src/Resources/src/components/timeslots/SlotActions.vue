<template>
  <div class="grid grid-cols-3 ">
    <button>edit</button>
    <button>delete</button>
    <Switch
      :sr="'Enable TimeSlot'"
      :initial="initial"
      @toggle="(state) => enabled = state"
    />
  </div>
</template>

<script setup>
import Switch from "@/components/misc/Switch.vue"
import {ref, watch} from "vue"
import {timeSlotStore} from "@/stores/timeSlotStore";

const props = defineProps(['timeSlot', 'timeSlotID'])
const initial = ref(props.timeSlot.enabled)
const enabled = ref(initial.value)

watch(enabled, () => {
  timeSlotStore.changeDisabledState(props.timeSlotID, enabled.value)
  .then((success) => {if(success === false) {
    initial.value = enabled.value
  }})
})

</script>