<template>
  <form
    class="relative grid w-[300px] grid-cols-6 grid-rows-4 gap-2 p-4 sm:w-[400px]"
    @submit.prevent="onSubmit()"
  >
    <h3 class="col-span-6 col-start-1 text-center">
      {{ header }}
    </h3>
    <InputLabel
      v-model="titleInput"
      :label-text="t('slot.slotTitle')"
      class="col-span-6 col-start-1"
      :required="required"
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
      :value="t('slot.save')"
      class="col-span-6 col-start-1 mx-2 mb-6 mt-4 flex h-9 cursor-pointer items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold leading-[10px] text-white drop-shadow-btn transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
      :class="activeBtnStyle"
      @mousedown="isMouseDown = true"
      @mouseup="isMouseDown = false"
    >
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import InputLabel from '@/components/misc/InputLabel.vue';
import { TimeSlot } from '@/stores/timeSlotStore';
import { useTimeSlots } from '@/stores/timeSlotStore';
import SubmitButton from '../misc/SubmitButton.vue';
import { ref } from 'vue';

const { t } = useI18n();
const { createSlot, editSlot } = useTimeSlots();

const props = withDefaults(defineProps<{
  header: string,
  title?: string,
  limit?: string,
  order?: string,
  submit: string,
  edit?: boolean,
  id: number,
}>(),{
  title: '',
  limit: '0',
  order: '0',
  edit: false,
});

const titleInput = ref(props.title);
const limitInput = ref(props.limit);
const orderInput = ref(props.order);
const required = ref(false);

async function onSubmit() {
  required.value = true;
  if (titleInput.value === '') {
    return;
  }
  const timeSlot: TimeSlot = {
    title: titleInput.value,
    limit: parseInt(limitInput.value),
    order: parseInt(orderInput.value),
    enabled: true,
    slug: null
  }
  if (props.edit === true) {
    await editSlot(props.id, timeSlot);
  } else {
    await createSlot(timeSlot);
  }
}
</script>