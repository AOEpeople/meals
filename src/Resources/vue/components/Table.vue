<template>
  <div class="mt-8 flex flex-col max-w-screen-aoe mx-auto">
    <div class="-my-2">
      <div class="inline-block min-w-full py-2 align-middle">
        <table class="min-w-full border-separate border-spacing-none">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col"
                  class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  v-for="label in props.labels"
                  :key="label"
              >
                {{ label }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <slot></slot>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps([
    'labels',
])

</script>

<style scoped>
.border-spacing-none {
  border-spacing: 0
}

</style>