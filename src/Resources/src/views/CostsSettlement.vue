<template>
  <div class="mx-[5%] flex w-full flex-col xl:mx-auto">
    <h2 class="w-full text-left max-[380px]:text-[24px]">
      {{ t('costs.settlementTitle') }}
    </h2>
    <span
      v-if="profile !== null"
      class="w-full"
    >
      {{ t('costs.settlementMessage').replace('#name#', profile.fullName) }}
    </span>
    <CreateButton
      v-if="isConfirmed === false && profile !== null"
      :btn-text="t('costs.confirm')"
      :managed="true"
      class="ml-0 w-fit cursor-pointer justify-self-center"
      @click="handleClick"
    />
    <span v-else-if="profile === null && loaded === true && isConfirmed === false">
      {{ t('costs.settlementNotPossible') }}
    </span>
    <span
      v-else-if="isConfirmed === true && profile !== null"
      class="w-full pt-4"
    >
      {{ t('costs.success').replace('#name#', profile.fullName) }}
    </span>
    <LoadingSpinner :loaded="loaded" />
  </div>
</template>

<script setup lang="ts">
import { type IProfile, useProfiles } from '@/stores/profilesStore';
import { onMounted, ref } from 'vue';
import { useCosts } from '@/stores/costsStore';
import CreateButton from '@/components/misc/CreateButton.vue';
import { useI18n } from 'vue-i18n';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';

const { fetchProfileWithHash } = useProfiles(0);
const { confirmSettlement } = useCosts();
const { t } = useI18n();

const props = defineProps<{
  hash: string;
}>();

const profile = ref<IProfile | null>(null);
const isConfirmed = ref(false);
const loaded = ref(false);

onMounted(async () => {
  profile.value = await fetchProfileWithHash(props.hash);

  loaded.value = true;
});

async function handleClick() {
  if (isConfirmed.value === false && (await confirmSettlement(props.hash)) === true) {
    isConfirmed.value = true;
  }
}
</script>
