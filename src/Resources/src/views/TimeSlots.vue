<template>
  <div class="xl:mx-auto mx-[5%]">
    <SlotHeader />
    <Table v-if="!timeSlots.isLoading" :labels="tableLabels" class="mt-10 mb-5">
      <tr v-for="(timeSlot, id) in timeSlots.slots" :key="id" class="max-h-[62px] border-b-2 border-gray-200">
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
          <SlotActions :timeSlot="timeSlot" :timeSlotID="id"/>
        </td>
      </tr>
    </Table>
  </div>
</template>

<script setup>
import Table from '@/components/misc/Table.vue'
import SlotHeader from '@/components/timeslots/SlotHeader.vue'
import SlotActions from '@/components/timeslots/SlotActions.vue'
import {useProgress} from '@marcoschulte/vue3-progress'
import {timeSlotStore} from "@/store/timeSlotStore"

const progress = useProgress().start()

timeSlotStore.fillStore()
const timeSlots = timeSlotStore.getState()

const tableLabels = {
  en: ['Title', 'Limit', 'Actions'],
  de: ['Title', 'Limit', 'Aktionen']
};

progress.finish()
</script>

<style scoped>

</style>