<template>
  <Popover
    :translate-x-min="'-1%'"
    :translate-x-max="'-55%'"
    :breakpoint-width="576"
  >
    <template #button="{ open }">
      <CreateButton
        :open="open"
        :btn-text="t('menu.addGuest')"
        data-cy="create-guest-btn"
      />
    </template>
    <template #panel="{ close }">
      <GuestForm
        v-model:first-name="guest.firstName"
        v-model:last-name="guest.lastName"
        v-model:company="guest.company"
        :filled="isFilled"
        :first-name-missing="guest.firstNameMissing"
        :last-name-missing="guest.lastNameMissing"
        :company-missing="false"
        :is-guest="false"
        class="w-screen p-4 text-start sm:w-[400px]"
        @submit-form="
          async () => {
            const isAdded = await addGuest();
            if (isAdded) close();
          }
        "
      >
        <h3 class="text-center">
          {{ t('guest.form.add') }}
        </h3>
      </GuestForm>
    </template>
  </Popover>
</template>

<script setup lang="ts">
import Popover from '@/components/misc/Popover.vue';
import CreateButton from '@/components/misc/CreateButton.vue';
import GuestForm from '@/components/guest/GuestForm.vue';
import type { Guest } from '@/api/postCreateGuest';
import postCreateGuest from '@/api/postCreateGuest';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import type { IMessage } from '@/interfaces/IMessage';
import { useProfiles, type IProfile } from '@/stores/profilesStore';
import { computed, reactive } from 'vue';
import { useParticipations } from '@/stores/participationsStore';
import { useI18n } from 'vue-i18n';

interface GuestForm extends Guest {
  firstNameMissing: boolean;
  lastNameMissing: boolean;
}

const props = defineProps<{
  weekId: number;
}>();

const { t } = useI18n();
const { sendFlashMessage } = useFlashMessage();
const { fetchAbsentingProfiles } = useProfiles(props.weekId);
const { addEmptyParticipationToState } = useParticipations(props.weekId);

const guest = reactive<GuestForm>({
  firstName: '',
  lastName: '',
  company: '',
  firstNameMissing: false,
  lastNameMissing: false
});

const isFilled = computed(() => guest.firstName !== '' && guest.lastName !== '');

async function addGuest() {
  if (isFilled.value) {
    const { error, response } = await postCreateGuest({
      firstName: guest.firstName,
      lastName: guest.lastName,
      company: guest.company
    });
    if (error.value) {
      sendFlashMessage({
        type: FlashMessageType.ERROR,
        message: (response.value as IMessage).message ?? 'Unknown error'
      });
      return false;
    } else {
      await fetchAbsentingProfiles();
      addEmptyParticipationToState({
        ...response.value,
        fullName: (response.value as IProfile).fullName + ' (Guest)'
      } as IProfile);
    }
    return true;
  } else {
    guest.firstNameMissing = guest.firstName === '';
    guest.lastNameMissing = guest.lastName === '';
    return false;
  }
}
</script>
