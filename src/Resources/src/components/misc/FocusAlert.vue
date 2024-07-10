<template>
  <div>
    <PopupModal
      :isOpen="isOpen"
    >
      <div
        class="max-w-[300px] p-4"
      >
        <p
          class="max-w-[300px] text-center align-middle font-bold"
        >
          __Session_abgelaufen_text_Platzhalter__
        </p>
        <CreateButton
          btnText="Reload"
          :managed=true
          @click="isOpen = false"
        />
      </div>
    </PopupModal>
    </div>
</template>

<script setup lang="ts">
import PopupModal from '@/components/misc/PopupModal.vue';
import CreateButton from '@/components/misc/CreateButton.vue';
import { onMounted, ref } from 'vue';

const TEN_MINUTES_MILLIS = 600000

const isOpen = ref(false);
const timestamp = ref<number>(0);

onMounted(() => {
  console.log('Mounted FocusAlert')
  timestamp.value = Date.now();
  window.addEventListener('focus', async () => {
    console.log('received focus!');
    if (Date.now() - timestamp.value > TEN_MINUTES_MILLIS) {
      try {
        timestamp.value = Date.now();
        await fetch(window.location.href);
      } catch (error) {
        isOpen.value = true;
      }
    }
  });
});
</script>