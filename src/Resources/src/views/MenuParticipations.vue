<template>
  <div>
    <h2>Menu Participations</h2>
    <h4>{{ `For Week with ID: ${week}` }}</h4>
    <MenuTable
      :week-id="parseInt(week)"
    />
  </div>
</template>

<script setup lang="ts">
import MenuTable from '@/components/menuParticipants/MenuTable.vue';

defineProps<{
  week: string;
}>();

</script>