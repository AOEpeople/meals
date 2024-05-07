<template>
  <div
    class="mb-8 grid w-full grid-cols-3 gap-6 sm:grid-rows-[minmax(0,1fr)_minmax(0,1fr)] min-[900px]:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]"
  >
    <h2
      class="col-span-3 col-start-1 row-span-1 row-start-1 m-0 flex w-full flex-row items-center gap-2 self-center justify-self-start max-[380px]:text-[24px] min-[900px]:col-span-1"
    >
      {{ t('costs.header') }}
      <ActionButton
        :btn-text="''"
        :action="Action.DOWNLOAD"
        :class="printActive === false ? 'text-gray': ''"
        @click="printActive && emit('printCosts')"
      />
    </h2>
    <InputLabel
      v-model="filter"
      :label-text="t('costs.search')"
      :label-visible="false"
      :x-button-active="true"
      class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
    />
    <CashRegisterLink
      v-if="userDataStore.roleAllowsRoute('CashRegister')"
      class="col-span-3 row-start-3 justify-self-center sm:col-span-1 sm:col-start-2 sm:row-start-2 sm:justify-self-end min-[900px]:row-start-1"
    />
    <SwitchGroup>
      <div
        class="col-span-3 row-start-4 grid grid-rows-[auto_minmax(0,1fr)] content-center justify-items-end sm:col-span-1 sm:col-start-3 sm:row-start-2 sm:justify-self-end sm:text-end min-[900px]:col-start-2"
      >
        <SwitchLabel class="w-full pb-1 text-end text-xs font-medium text-[#173D7A]">
          {{ t('costs.showHidden') }}
        </SwitchLabel>
        <Switch
          :sr="t('costs.showHidden')"
          :initial="showHidden"
          class="self-end justify-self-end"
          @toggle="(value) => emit('change:showHidden', value)"
        />
      </div>
    </SwitchGroup>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import CashRegisterLink from './CashRegisterLink.vue';
import InputLabel from '../misc/InputLabel.vue';
import { computed } from 'vue';
import Switch from '@/components/misc/Switch.vue';
import { SwitchGroup, SwitchLabel } from '@headlessui/vue';
import { userDataStore } from '@/stores/userDataStore';
import ActionButton from '../misc/ActionButton.vue';
import { Action } from '@/enums/Actions';

const { t } = useI18n();

const props = defineProps<{
  modelValue: string;
  showHidden: boolean;
  printActive: boolean;
}>();

const emit = defineEmits(['update:modelValue', 'change:showHidden', 'printCosts']);

const filter = computed({
  get() {
    return props.modelValue;
  },
  set(filter) {
    emit('update:modelValue', filter);
  }
});
</script>
