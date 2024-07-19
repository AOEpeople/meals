<template>
  <div
    class="flex flex-col place-content-center text-center"
    :style="{ height: componentHeight }"
  >
    <h1 class="pt-10">
      {{ weekDay }}
    </h1>
    <h3 class="pb-10">
      {{ dateStr }}
    </h3>
    <img
      class="mx-auto aspect-[228/131]"
      src="../../../images/empty_meal.png"
      :style="{ width: imgHeight }"
    />
    <h3 class="pt-10">
      {{ t('participantList.no_meals') }}
    </h3>
  </div>
</template>

<script setup lang="ts">
import { DateTime } from '@/api/getDashboardData';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useComponentHeights } from '@/services/useComponentHeights';

const props = defineProps<{
  day: DateTime;
}>();

const { t, locale } = useI18n();
const { maxNoParticipationsHeight, windowWidth } = useComponentHeights();

const weekDay = computed(() => new Date(props.day.date).toLocaleDateString(locale.value, { weekday: 'long' }));
const dateStr = computed(() =>
  new Date(props.day.date).toLocaleDateString(locale.value, { year: 'numeric', month: 'long', day: 'numeric' })
);
const componentHeight = computed(() => {
  return `${maxNoParticipationsHeight.value}px`;
});
const imgHeight = computed(() =>
  maxNoParticipationsHeight.value > windowWidth.value
    ? `${windowWidth.value / 3}px`
    : `${maxNoParticipationsHeight.value / 3}px`
);
</script>
