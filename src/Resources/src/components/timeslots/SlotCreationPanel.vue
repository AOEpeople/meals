<template>
  <form
    class="z-[102] grid grid-cols-6 grid-rows-3 p-4 xl:w-[800px]"
    @submit.prevent="onSubmit()"
  >
    <h3 class="col-span-6 col-start-1">
      {{ t('slot.createHeader') }}
    </h3>
    <InputLabel
      v-model="titleInput"
      :label-text="t('slot.slotTitle')"
      class="col-span-4 col-start-1"
    />
    <InputLabel
      v-model="limitInput"
      :label-text="t('slot.slotLimit')"
      type="number"
      class="col-span-1 col-start-5"
    />
    <InputLabel
      v-model="orderInput"
      :label-text="t('slot.slotOrder')"
      type="number"
      class="col-span-1 col-start-6"
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
const { createSlot } = useTimeSlots();

const titleInput = ref('');
const limitInput = ref('0');
const orderInput = ref('0');

async function onSubmit() {
  const timeSlot: TimeSlot = {
    title: titleInput.value,
    limit: parseInt(limitInput.value),
    order: parseInt(orderInput.value),
    enabled: true
  }
  // console.log(`Submit timeslot for creation: { title: ${timeSlot.title}, limit: ${timeSlot.limit}, order: ${timeSlot.order}, enabled: ${timeSlot.enabled} }`);
  await createSlot(timeSlot);
}
</script>