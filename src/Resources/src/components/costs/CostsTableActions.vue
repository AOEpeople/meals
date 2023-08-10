<template>
  <div class="ml-auto grid h-full w-fit grid-cols-3 justify-end gap-2">
    <ActionButton
      :action="Action.HIDE"
      :btn-text="''"
      @click="hideUser(username)"
    />
    <ActionButton
      :action="Action.CREATE"
      :btn-text="''"
    />
    <ActionButton
      v-if="balance > 0"
      :action="Action.BALANCE"
      :btn-text="''"
      @click="settlementOpen = true"
    />
  </div>
  <CostsActionSettlement
    :open="settlementOpen"
    :username="username"
    @confirm="(value) => handleSettlement(value)"
  />
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import { useCosts } from '@/stores/costsStore';
import CostsActionSettlement from './CostsActionSettlement.vue';
import { ref } from 'vue';

const { hideUser, sendSettlement } = useCosts();

const settlementOpen = ref(false);

const props = defineProps<{
  username: string,
  balance: number
}>();

async function handleSettlement(confirm: boolean) {
  settlementOpen.value = false;

  if (confirm === true) {
    await sendSettlement(props.username);
  }
}
</script>