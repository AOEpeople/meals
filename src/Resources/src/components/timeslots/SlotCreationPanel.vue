<template>
  <form
    class="relative grid w-[300px] grid-cols-6 grid-rows-4 gap-2 p-4 sm:w-[400px]"
    @submit.prevent="onSubmit()"
  >
    <h3 class="col-span-6 col-start-1">
      {{ header }}
    </h3>
    <InputLabel
      v-model="titleInput"
      :label-text="t('slot.slotTitle')"
      class="col-span-6 col-start-1"
    />
    <InputLabel
      v-model="limitInput"
      :label-text="t('slot.slotLimit')"
      type="number"
      class="col-span-3 col-start-1"
    />
    <InputLabel
      v-model="orderInput"
      :label-text="t('slot.slotOrder')"
      type="number"
      class="col-span-3 col-start-4"
    />
    <input
      type="submit"
      value="Submit"
      class="col-span-6 col-start-1"
    >
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import InputLabel from "@/components/misc/InputLabel.vue";
import { ref } from 'vue';
import { TimeSlot } from '@/stores/timeSlotStore';
import { useTimeSlots } from '@/stores/timeSlotStore';

const { t } = useI18n();
const { createSlot, editSlot } = useTimeSlots();

const props = withDefaults(defineProps<{
  header: string,
  title?: string,
  limit?: string,
  order?: string,
  submit: string,
  edit?: boolean,
  id: number
}>(),{
  title: "",
  limit: '0',
  order: '0',
  edit: false
});

const titleInput = ref(props.title);
const limitInput = ref(props.limit);
const orderInput = ref(props.order);

async function onSubmit() {
  const timeSlot: TimeSlot = {
    title: titleInput.value,
    limit: parseInt(limitInput.value),
    order: parseInt(orderInput.value),
    enabled: true
  }
  if(props.edit) {
    await editSlot(props.id, timeSlot);
  } else {
    await createSlot(timeSlot);
  }
}
</script>