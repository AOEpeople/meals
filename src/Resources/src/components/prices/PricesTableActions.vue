<template>
  <div class="flex flex-row content-center items-center justify-end justify-items-end gap-2 sm:gap-4">
    <ActionButton
      :id="`edit-${props.year}-price-button`"
      v-if="canEdit"
      :action="Action.EDIT"
      :btn-text="t('button.edit')"
      class="h-[40px]"
      @click="$emit('edit', year)"
    />
    <ActionButton
      :id="`delete-${props.year}-price-button`"
      v-if="canDelete"
      :action="Action.DELETE"
      :btn-text="t('button.delete')"
      :width-full="false"
      class="h-[40px]"
      @click="$emit('delete', year)"
    />
  </div>
</template>

<script setup lang="ts">
import ActionButton from '../misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps<{
  year: number;
}>();

defineEmits<{
  edit: [year: number];
  delete: [year: number];
}>();

const currentYear = new Date().getFullYear();

const canEdit = computed(() => props.year >= currentYear);
const canDelete = computed(() => props.year > currentYear);
</script>
