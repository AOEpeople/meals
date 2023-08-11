<template>
  <div>{{ `Settlement for hash: ${hash}` }}</div>
  <span
    v-if="profile !== null"
  >
    {{ `And user: ${profile.fullName}` }}
  </span>
</template>

<script setup lang="ts">
import { IProfile, useProfiles } from '@/stores/profilesStore';
import { onMounted, ref } from 'vue';

const { fetchProfileWithHash } = useProfiles(0);

const props = defineProps<{
  hash: string
}>();

const profile = ref<IProfile>(null);

onMounted(async () => {
  profile.value = await fetchProfileWithHash(props.hash);
});
</script>