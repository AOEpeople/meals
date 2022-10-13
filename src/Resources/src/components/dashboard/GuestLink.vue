<template>
  <div class="bg-white p-4 border-2 rounded">
    <span>{{ url }}</span>
    <span class="text-green">
      <CheckIcon class="w-4" />
      {{ t('dashboard.guest') }}
    </span>
  </div>
</template>

<script setup>
import {useGuestLink} from "@/hooks/getGuestLink";
import {useI18n} from "vue-i18n";
import { CheckIcon } from "@heroicons/vue/solid";

const props = defineProps(['dayID'])
const { t } = useI18n()

let url = ''

const { link, error } = await useGuestLink(props.dayID)
if (error.value === false) {
  copyTextToClipboard(link.value.url)
  url = link.value.url
}

async function fallbackCopyTextToClipboard(text) {
  var textArea = document.createElement("textarea");
  textArea.value = text;

  // Avoid scrolling to bottom
  textArea.style.top = "0";
  textArea.style.left = "0";
  textArea.style.position = "fixed";

  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Fallback: Copying text command was ' + msg);
  } catch (err) {
    console.error('Fallback: Oops, unable to copy', err);
  }

  document.body.removeChild(textArea);
}
async function copyTextToClipboard(text) {
  if (!navigator.clipboard) {
    await fallbackCopyTextToClipboard(text);
    return;
  }
  navigator.clipboard.writeText(text).then(function() {
    console.log('Async: Copying to clipboard was successful!');
  }, function(err) {
    console.error('Async: Could not copy text: ', err);
  });
}

</script>

<style scoped>

</style>