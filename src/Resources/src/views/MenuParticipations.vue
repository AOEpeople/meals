<template>
  <div>
    <h2>{{ t('menu.participations') }}</h2>
    <h4>{{ `For Week with ID: ${week}` }}</h4>
    <div class="mx-2 w-[300px]">
      <AddParticipantsSearchBar
        :week-id="parseInt(week)"
        @profile-selected="handleProfileSelect"
      >
        <template #label>
          <span class="w-full px-4 text-start text-xs font-medium text-[#173D7A]">
            Add a Participant
          </span>
        </template>
      </AddParticipantsSearchBar>
    </div>
    <div
      class="mb-8 mt-4 max-w-screen-aoe overflow-x-auto"
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

const props = defineProps<{
  week: string;
}>();

const { t } = useI18n();
const { addEmptyParticipationToState } = useParticipations(parseInt(props.week));

function handleProfileSelect(profile: IProfile) {
  addEmptyParticipationToState(profile);
  console.log(`Selected Profile(${profile.user}) with name: ${profile.fullName}, roles: [${profile.roles.join(', ')}] was added to the State!`);
}

</script>