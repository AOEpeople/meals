<template>
  <h2>
    {{ t('guest.event.title') }}
  </h2>
  <p>
    {{ t('guest.event.description').replace('%EventTitle%', invitationData?.event) }}
  </p>
  <div>
    <form
      @submit.prevent="handleSubmit()"
    >
      <InputLabel
        v-model="formData.firstname"
        :required="true"
        :labelText="t('guest.form.firstname')"
      />
      <InputLabel
        v-model="formData.lastname"
        :required="true"
        :labelText="t('guest.form.lastname')"
      />
      <InputLabel
        v-model="formData.company"
        :labelText="t('guest.form.company')"
      />
      <SubmitButton
        :btnText="t('guest.event.submit')"
      />
    </form>
  </div>
</template>

<script setup lang="ts">
import getEventInvitationData, { EventInvitationData } from '@/api/getEventInvitionData';
import InputLabel from '@/components/misc/InputLabel.vue';
import SubmitButton from '@/components/misc/SubmitButton.vue';
import { isMessage } from '@/interfaces/IMessage';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const invitationData = ref<EventInvitationData | null>(null);

const formData = ref({
  firstname: '',
  lastname: '',
  company: ''
});

const props = defineProps<{
  hash: string
}>()

onMounted(async () => {
  const { error, response } = await getEventInvitationData(props.hash);

  if (error.value === false && !isMessage(response)) {
    invitationData.value = (response.value as EventInvitationData);
  }
});

async function handleSubmit() {
  console.log('HUHU')
}
</script>