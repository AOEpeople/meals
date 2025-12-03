<template>
  <div
    v-if="invitationData !== null && hasJoined === false"
    class="mx-[5%] xl:mx-auto"
  >
    <h2 class="text-center text-primary xl:text-left">
      {{ `${t('guest.event.title')} | ${eventDate}` }}
    </h2>
    <p class="whitespace-pre-line text-[18px] leading-[24px] text-primary-1">
      {{ t('guest.event.description').replace('%EventTitle%', invitationData?.event).replace('%lockDate%', lockDate) }}
    </p>
    <div>
      <form @submit.prevent="handleSubmit()">
        <InputLabel
          v-model="formData.firstName"
          :required="true"
          :labelText="t('guest.form.firstname')"
        />
        <InputLabel
          v-model="formData.lastName"
          :required="true"
          :labelText="t('guest.form.lastname')"
        />
        <InputLabel
          v-model="formData.company"
          :labelText="t('guest.form.company')"
        />
        <SubmitButton :btnText="t('guest.event.submit')" />
      </form>
    </div>
  </div>
  <div
    v-else-if="invitationData !== null && hasJoined === true"
    class="mx-[5%] xl:mx-auto"
  >
    <h2 class="text-center text-primary xl:text-left">
      {{ `${t('guest.event.title')} | ${eventDate}` }}
    </h2>
    <p class="whitespace-pre-line text-[18px] leading-[24px] text-primary-1">
      {{ t('guest.event.joined').replace('%EventTitle%', invitationData?.event).replace('%eventDate%', eventDate) }}
    </p>
  </div>
  <LoadingSpinner
    v-else
    :loaded="invitationData === null"
  />
</template>

<script setup lang="ts">
import getEventInvitationData, { type EventInvitationData } from '@/api/getEventInvitionData';
import postJoinEventGuest, { type GuestEventData } from '@/api/postJoinEventGuest';
import InputLabel from '@/components/misc/InputLabel.vue';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import SubmitButton from '@/components/misc/SubmitButton.vue';
import { FlashMessageType } from '@/enums/FlashMessage';
import { isMessage } from '@/interfaces/IMessage';
import useFlashMessage from '@/services/useFlashMessage';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const { sendFlashMessage } = useFlashMessage();

const invitationData = ref<EventInvitationData | null>(null);

const formData = ref<GuestEventData>({
  firstName: '',
  lastName: '',
  company: ''
});

const hasJoined = ref(false);

const props = defineProps<{
  hash: string;
}>();

onMounted(async () => {
  const { error, response } = await getEventInvitationData(props.hash);

  if (error.value === false && !isMessage(response)) {
    invitationData.value = response.value as EventInvitationData;
  }
});

const eventDate = computed(() => {
  if (invitationData.value !== null) {
    return getLocaleDateRepr(invitationData.value.date.date);
  }
  return 'unknown';
});

const lockDate = computed(() => {
  if (invitationData.value !== null) {
    return getLocaleDateRepr(invitationData.value.lockDate.date, true);
  }
  return 'unknown';
});

async function handleSubmit() {
  const { error, response } = await postJoinEventGuest(props.hash, formData.value);

  if (error.value === true && isMessage(response.value) && response.value.message.includes('already joined')) {
    sendFlashMessage({
      type: FlashMessageType.ERROR,
      message: response.value.message,
      hasLifetime: true
    });
  } else if (error.value === true) {
    sendFlashMessage({
      type: FlashMessageType.ERROR,
      message: 'Error occured',
      hasLifetime: true
    });
  } else {
    hasJoined.value = true;
    sendFlashMessage({
      type: FlashMessageType.INFO,
      message: 'guest.joined',
      hasLifetime: true
    });
  }
}

function getLocaleDateRepr(date: string, getTime = false) {
  if (getTime === true) {
    return new Date(date).toLocaleDateString(locale.value, {
      weekday: 'long',
      month: 'numeric',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  return new Date(date).toLocaleDateString(locale.value, { weekday: 'long', month: 'numeric', day: 'numeric' });
}
</script>
