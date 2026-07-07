<template>
  <div class="ml-auto grid h-full w-fit grid-cols-3 justify-end gap-2">
    <ActionButton
      class="min-w-[32px]"
      :action="Action.HIDE"
      :btn-text="''"
      @click="hideUser(props.userid)"
    />
    <Popover
      :overflow-hidden="false"
      :translate-x-min="'-5%'"
      :popup-styles="'right-0'"
    >
      <template #button>
        <ActionButton
          :action="Action.CREATE"
          :btn-text="''"
        />
      </template>
      <template #panel="{ close }">
        <CashPaymentPanel
          :userid="props.userid"
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
    :userid="props.userid"
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
  userid: number;
  balance: number;
}>();

async function handleSettlement(confirm: boolean) {
  settlementOpen.value = false;

  if (confirm === true) {
    await sendSettlement(props.userid);
  }
}
</script>
