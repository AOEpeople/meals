<template>
  <div>
    <PopupModal :isOpen="isOpen">
      <div class="max-w-[300px] p-4">
        <h4 class="text-center align-middle">
          {{ t('session.timeout_header') }}
        </h4>
        <p class="text-center align-middle">
          {{ t('session.timeout_text') }}
        </p>
        <CreateButton
          class="cursor-pointer"
          :btnText="t('session.reload')"
          :managed="true"
          @click="handleReload()"
        />
      </div>
    </PopupModal>
  </div>
</template>

<script setup lang="ts">
import PopupModal from '@/components/misc/PopupModal.vue';
import CreateButton from '@/components/misc/CreateButton.vue';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { saveAndReload, isSessionActive } from '@/tools/checkActiveSession';

const { t } = useI18n();

// 600.000
const TEN_MINUTES_MILLIS = 600000;

const isOpen = ref(false);
const timestamp = ref<number>(0);

onMounted(() => {
  timestamp.value = Date.now();
  window.addEventListener('focus', () => checkSessionCallback());
});

function handleReload() {
  isOpen.value = false;
  saveAndReload();
}

async function checkSessionCallback() {
  if (Date.now() - timestamp.value > TEN_MINUTES_MILLIS) {
    try {
      timestamp.value = Date.now();
      const sessionActive = await isSessionActive();
      isOpen.value = !sessionActive;
    } catch (error) {
      isOpen.value = true;
    }
  }
}
</script>
