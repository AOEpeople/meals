<template>
  <div class="max-[1000px]:px-2">
    <h2>{{ t('menu.participations') }}</h2>
    <div class="grid w-full min-[650px]:grid-cols-[minmax(300px,400px)_1fr_minmax(300px,400px)]">
      <AddParticipantsSearchBar
        :week-id="parseInt(week)"
        class="col-start-1"
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
        class="max-[650px]:row-start-2 min-[650px]:col-start-3"
      />
    </div>
    <div
      class="my-8 max-w-screen-aoe overflow-x-auto"
    >
      <MenuTable
        :week-id="parseInt(week)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import AddParticipantsSearchBar from '@/components/menuParticipants/AddParticipantsSearchBar.vue';
import MenuTable from '@/components/menuParticipants/MenuTable.vue';
import { IProfile } from '@/stores/profilesStore';
import { useI18n } from 'vue-i18n';
import { useParticipations } from '@/stores/participationsStore';
import InputLabel from '@/components/misc/InputLabel.vue';
import { ref, watch } from 'vue';

const props = defineProps<{
  week: string;
}>();

const { t } = useI18n();
const { addEmptyParticipationToState, setFilter } = useParticipations(parseInt(props.week));

const participantFilter = ref('');

watch(
  () => participantFilter.value,
  () => {
    setFilter(participantFilter.value);
  }
);

function handleProfileSelect(profile: IProfile) {
  addEmptyParticipationToState(profile);
}

</script>