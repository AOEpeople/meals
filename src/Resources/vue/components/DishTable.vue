<template>
  <div class="mt-8 flex flex-col max-w-screen-aoe mx-auto">
    <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
      <div class="inline-block min-w-full py-2 align-middle">
        <table class="min-w-full border-separate border-spacing-none">
          <thead class="bg-gray-50">
          <tr>
            <th scope="col"
                class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                v-for="label in labels"
                :key="label"
            >
              {{ label }}
            </th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
          <tr v-for="(dataset, index) in props.data" :key="index">
            <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500"
                v-for="element in dataset"
            >
              {{ element }}
            </td>
            <td v-if="props.actions" class="whitespace-nowrap py-4 px-3">
              <Actions :data="dataset" />
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import Actions from '@/components/content/disclosures/Actions.vue'
const props = defineProps([
  'labels',
  'data',
  'actions',
])

let labels = props.labels

if(props.actions) {
  labels.push('Actions')
}

</script>

<style scoped>
.border-spacing-none {
  border-spacing: 0
}
</style>