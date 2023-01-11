<template>
  <div class="text-center">
    <h1 class="m-0">
      List of dishes
    </h1>
    <Disclosure>
      <DisclosureButton class="btn-secondary">
        CREATE DISH
      </DisclosureButton>
      <DisclosurePanel>
        <ModifyDishes />
      </DisclosurePanel>
    </Disclosure>
  </div>
  <Table
    :labels="tableLabels"
    :data="tableData"
    :actions="true"
  />
</template>

<script setup>
import {Disclosure, DisclosureButton, DisclosurePanel} from '@headlessui/vue'
import ModifyDishes from '@/components/dishes/ModifyDishes.vue'
import {useProgress} from '@marcoschulte/vue3-progress'
import Table from '@/components/misc/Table.vue'

const progress = useProgress().start();

const tableLabels = ['Title', 'Category'];
const tableData = [
  { title_en: 'Dish1', title_de: 'Gericht1', desc_en: 'Description', desc_de: 'Beschreibung', category: 'Meat' },
  { title_en: 'Dish2', title_de: 'Gericht2', desc_en: 'Description', desc_de: 'Beschreibung', category: 'Fish' },
  { title_en: 'Dish3', title_de: 'Gericht3', desc_en: 'Description', desc_de: 'Beschreibung', category: 'Vegetarian' },
]

progress.finish()
</script>

<style scoped>

</style>