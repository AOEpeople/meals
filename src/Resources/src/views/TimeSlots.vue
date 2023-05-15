<template>
  <div class="mx-[5%] xl:mx-auto">
    <SlotHeader />
    <Table
      v-if="!TimeSlotState.isLoading"
      :labels="[t('slot.slotTitle'), t('slot.slotLimit'), t('slot.slotActions')]"
      class="mb-5 mt-10"
    >
      <tr
        v-for="(timeSlot, id) in TimeSlotState.timeSlots"
        :key="id"
        class="max-h-[62px] border-b-2 border-gray-200"
      >
        <td>
          <span class="text-[12px] xl:text-[18px]">
            {{ timeSlot.title }}
          </span>
        </td>
        <td>
          <span class="text-[12px] xl:text-[18px]">
            {{ timeSlot.limit }}
          </span>
        </td>
        <td>
          <SlotActions
            :timeSlot="timeSlot"
            :timeSlotID="Number(id)"
          />
        </td>
      </tr>
    </Table>
  </div>
</template>

<script setup lang="ts">
import Table from '@/components/misc/Table.vue';
import SlotHeader from '@/components/timeslots/SlotHeader.vue';
import SlotActions from '@/components/timeslots/SlotActions.vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import { useTimeSlots } from "@/stores/timeSlotStore";
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { TimeSlotState, fetchTimeSlots } = useTimeSlots();
const { t } = useI18n();

onMounted(async () => {
  const progress = useProgress().start();
  await fetchTimeSlots();
  progress.finish();
});
</script>

<style scoped>

</style>