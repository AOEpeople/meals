<template>
  <div class="z-0 max-[1000px]:px-2">
    <div class="flex flex-col justify-between text-center sm:flex-row">
      <h2>{{ t('menu.participations') }}</h2>
      <GuestCreationPanel :week-id="parseInt(week)" />
    </div>
    <div class="z-20 grid w-full min-[650px]:grid-cols-[minmax(300px,400px)_1fr_minmax(300px,400px)]">
      <AddParticipantsSearchBar
        :week-id="parseInt(week)"
        class="z-[99] col-start-1"
        @profile-selected="handleProfileSelect"
      >
        <template #label>
          <span class="w-full px-4 text-start text-xs font-medium text-[#173D7A]">
            {{ t('menu.add') }}
          </span>
        </template>
      </AddParticipantsSearchBar>
      <InputLabel
        v-model="participantFilter"
        :label-text="t('menu.search')"
        :label-visible="true"
        :x-button-active="true"
        class="max-[650px]:row-start-2 min-[650px]:col-start-3"
      />
    </div>
    <div class="z-10 my-8 max-w-screen-aoe overflow-x-auto">
      <MenuTable :week-id="parseInt(week)" />
    </div>
  </div>
</template>

<script setup lang="ts">
import AddParticipantsSearchBar from '@/components/menuParticipants/AddParticipantsSearchBar.vue';
import MenuTable from '@/components/menuParticipants/MenuTable.vue';
import { type IProfile } from '@/stores/profilesStore';
import { useI18n } from 'vue-i18n';
import { useParticipations } from '@/stores/participationsStore';
import InputLabel from '@/components/misc/InputLabel.vue';
import { ref, watch } from 'vue';
import { refThrottled } from '@vueuse/core';
import GuestCreationPanel from '@/components/guest/GuestCreationPanel.vue';

const props = defineProps<{
  week: string;
}>();

const { t } = useI18n();
const { addEmptyParticipationToState, setFilter } = useParticipations(parseInt(props.week));

const participantFilter = ref('');
const filter = refThrottled(participantFilter, 1000);

watch(
  () => filter.value,
  () => {
    setFilter(participantFilter.value);
  }
);

function handleProfileSelect(profile: IProfile) {
  addEmptyParticipationToState(profile);
}
</script>
