<template>
  <div
    ref="content"
    :hidden="contentHidden"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { useI18n } from 'vue-i18n';
import useHtml2Pdf from '@/services/useHtml2Pdf';

const content = ref<HTMLDivElement | null>(null);
const { t } = useI18n();
const { generatePdf } = useHtml2Pdf();

const props = withDefaults(
  defineProps<{
    filename: string;
    contentHidden?: boolean;
  }>(),
  {
    contentHidden: false
  }
);

async function downloadPdf() {
  if (content.value !== null) {
    content.value.hidden = false;
    await generatePdf(content.value, props.filename);
    content.value.hidden = props.contentHidden;
  } else {
    useFlashMessage().sendFlashMessage({
      type: FlashMessageType.ERROR,
      message: t('flashmessage.print.error')
    });
  }
}

defineExpose({ downloadPdf });
</script>
