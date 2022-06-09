<template>
  <div>
    <Disclosure>
      <DisclosureButton>Add</DisclosureButton>
      <DisclosurePanel></DisclosurePanel>
    </Disclosure>

    <Disclosure>
      <DisclosureButton>Add</DisclosureButton>
      <DisclosurePanel>
        <ModifyDishes :data="dish" />
      </DisclosurePanel>
    </Disclosure>

    <Disclosure>
      <DisclosureButton>Add</DisclosureButton>
      <DisclosurePanel></DisclosurePanel>
    </Disclosure>
  </div>
</template>

<script setup>
import {Disclosure, DisclosureButton, DisclosurePanel} from "@headlessui/vue";
import ModifyDishes from "@/components/content/disclosures/ModifyDishes.vue";

</script>

<style scoped>

</style>