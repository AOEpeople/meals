<template>
  <div class="ml-auto grid h-full w-fit grid-cols-3 justify-end gap-2">
    <ActionButton
      class="min-w-[32px]"
      :action="Action.HIDE"
      :btn-text="''"
      @click="hideUser(username)"
    />
    <Popover
      :overflow-hidden="false"
      :translate-x-min="'-5%'"
      :popup-styles="'right-0'"
    >
      <template #button="{ open }">
        <ActionButton
          :action="Action.CREATE"
          :btn-text="''"
        />
      </template>
      <template #panel="{ close }">
        <CashPaymentPanel
          :username="username"
          @closePanel="close()"
        />
      </template>
    </Popover>
    <ActionButton
      v-if="balance > 0"
      class="min-w-[32px]"
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
import Popover from '../misc/Popover.vue';
import CashPaymentPanel from './CashPaymentPanel.vue';

const { hideUser, sendSettlement } = useCosts();

const settlementOpen = ref(false);

const props = defineProps<{
  username: string;
  balance: number;
}>();

async function handleSettlement(confirm: boolean) {
  settlementOpen.value = false;

  if (confirm === true) {
    await sendSettlement(props.username);
  }
}
</script>
