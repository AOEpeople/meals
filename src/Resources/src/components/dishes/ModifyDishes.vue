<template>
  <div class="bg-gray-300">
    <div class="grid grid-cols-2">
      <div class="grid grid-rows-3">
        <div>
          Deutsch
        </div>
        <div>
          <input
            type="text"
            placeholder="Neues Gericht"
          >
        </div>
        <div>
          <input
            type="text"
            placeholder="Beschreibung"
          >
        </div>
      </div>
      <div class="grid grid-rows-3">
        <div>
          English
        </div>
        <div>
          <input
            type="text"
            placeholder="New Dish"
            :value="undefined"
          >
        </div>
        <div>
          <input
            type="text"
            placeholder="Description"
          >
        </div>
      </div>
    </div>
    <div>
      <Listbox v-model="selectedCategory">
        <ListboxButton>{{ selectedCategory }}</ListboxButton>
        <ListboxOptions>
          <ListboxOption
            v-for="category in categories"
            :key="category"
            :value="category"
          >
            {{ category }}
          </ListboxOption>
        </ListboxOptions>
      </Listbox>
    </div>
    <button class="btn-primary">
      SAVE
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps([
    'data'
])
import {Listbox, ListboxButton, ListboxOption, ListboxOptions} from "@headlessui/vue";

const categories = ['No Category', 'Pasta', 'Soup', 'Fish']
const selectedCategory = ref(categories[0])

</script>
