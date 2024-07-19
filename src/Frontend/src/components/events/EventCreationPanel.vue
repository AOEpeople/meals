<template>
  <form
    class="w-[300px] p-4 sm:w-[450px] md:w-[550px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="text-center">
      {{ edit ? t('event.popover.update') : t('event.popover.create') }}
    </h3>
    <div class="flex flex-col gap-4 md:flex-row">
      <InputLabel
        v-model="eventTitle"
        :label-text="t('event.popover.title')"
        :required="required"
      />
      <SwitchGroup>
        <div class="flex flex-col items-start pt-2">
          <SwitchLabel
            class="h-5 min-w-fit whitespace-nowrap text-nowrap text-start text-xs font-medium text-[#173D7A]"
          >
            {{ t('event.popover.isPublic') }}
          </SwitchLabel>
          <Switch
            :sr="t('event.popover.isPublic')"
            :initial="isEventPublic"
            class="my-auto ml-4"
            @toggle="(val) => (isEventPublic = val)"
          />
        </div>
      </SwitchGroup>
    </div>
    <SubmitButton />
  </form>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import InputLabel from '../misc/InputLabel.vue';
import { ref } from 'vue';
import { SwitchGroup, SwitchLabel } from '@headlessui/vue';
import Switch from '@/components/misc/Switch.vue';
import SubmitButton from '../misc/SubmitButton.vue';
import { useEvents } from '@/stores/eventsStore';

const { t } = useI18n();
const { createEvent, updateEvent } = useEvents();

const props = withDefaults(
  defineProps<{
    title?: string;
    isPublic?: boolean;
    edit?: boolean;
    slug?: string;
  }>(),
  {
    title: '',
    isPublic: false,
    edit: false,
    slug: ''
  }
);

const emit = defineEmits(['closePanel']);

const eventTitle = ref(props.title);
const isEventPublic = ref(props.isPublic);

const required = ref(false);

async function onSubmit() {
  required.value = true;
  if (eventTitle.value !== '' && props.edit === false) {
    await createEvent(eventTitle.value, isEventPublic.value);
    emit('closePanel');
  } else if (eventTitle.value !== '' && props.slug !== '') {
    await updateEvent(props.slug, eventTitle.value, isEventPublic.value);
    emit('closePanel');
  }
}
</script>
