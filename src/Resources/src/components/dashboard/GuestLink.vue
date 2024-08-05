<template>
  <div class="rounded border-2 bg-white p-4">
    <span>{{ url }}</span>
    <div class="flex gap-2 text-green">
      <CheckIcon class="w-5 flex-initial" />
      <span class="flex-auto text-left">{{ t('dashboard.guest') }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useGuestLink } from '@/api/getGuestLink';
import getEventGuestLink from '@/api/getEventGuestLink';
import { useI18n } from 'vue-i18n';
import { CheckIcon } from '@heroicons/vue/solid';
import { onMounted, ref } from 'vue';
import { Invitation } from '@/enums/Invitation';

const props = defineProps<{
  dayID: string;
  invitation: Invitation;
}>();

const { t } = useI18n();
const url = ref('');

onMounted(async () => {
  const { link, error } =
    props.invitation === Invitation.MEAL ? await useGuestLink(props.dayID) : await getEventGuestLink(props.dayID);
  if (error.value === false && link.value !== undefined) {
    copyTextToClipboard(link.value.url);
    url.value = link.value.url;
  }
});

async function fallbackCopyTextToClipboard(text: string) {
  let textArea = document.createElement('textarea');
  textArea.value = text;

  // Avoid scrolling to bottom
  textArea.style.top = '0';
  textArea.style.left = '0';
  textArea.style.position = 'fixed';

  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  document.execCommand('copy');

  document.body.removeChild(textArea);
}

async function copyTextToClipboard(text: string) {
  if (!navigator.clipboard) {
    await fallbackCopyTextToClipboard(text);
    return;
  }
  navigator.clipboard.writeText(text).then(
    function () {
      console.log('Async: Copying to clipboard was successful!');
    },
    function (err) {
      console.error('Async: Could not copy text: ', err);
    }
  );
}
</script>
