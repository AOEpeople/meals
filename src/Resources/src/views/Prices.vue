<template>
  <div v-if="loaded && Object.keys(PricesState.prices).length > 0">
    <div class="mx-[5%] mt-6 xl:mx-auto">
      <PricesHeader />
      <PricesTable
        :prices="Object.values(PricesState.prices)"
        @edit="handleEdit"
        @delete="handleDelete"
      />

      <Modal
        :show="showEditPopover && editYear !== null"
        @close="closeEditPopover"
      >
        <PricesEditPopover
          v-if="editYear !== null"
          :key="editYear"
          :year="editYear"
          @update="handleUpdate"
          @close="closeEditPopover"
        />
      </Modal>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Modal from '@/components/misc/Modal.vue';
import PricesHeader from '@/components/prices/PricesHeader.vue';
import PricesTable from '@/components/prices/PricesTable.vue';
import PricesEditPopover from '@/components/prices/PricesEditPopover.vue';
import { usePrices } from '@/stores/pricesStore';

const { t } = useI18n();
const { fetchPrices, PricesState, updatePrice, deletePrice } = usePrices();
const loaded = ref(false);
const editYear = ref<number | null>(null);
const showEditPopover = ref(false);

function handleEdit(year: number) {
  editYear.value = year;
  showEditPopover.value = true;
}

async function handleUpdate(data: { year: number; price: number; price_combined: number }) {
  const success = await updatePrice(data);
  if (success) {
    closeEditPopover();
  }
}

function closeEditPopover() {
  showEditPopover.value = false;
  editYear.value = null;
}

async function handleDelete(year: number) {
  if (confirm(t('prices.confirmDelete', { year: year }))) {
    await deletePrice(year);
  }
}

onMounted(async () => {
  await fetchPrices();
  loaded.value = true;
});
</script>
