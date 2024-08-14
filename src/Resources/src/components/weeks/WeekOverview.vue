<template>
  <div
    class="group grid aspect-[16/10] h-full cursor-pointer grid-cols-[24px_minmax(0,1fr)] grid-rows-2 rounded-lg border-0 border-none bg-white text-center align-middle shadow-day transition-transform"
    :class="{ 'hover:scale-[115%]': week.id }"
    @click="handleClick"
  >
    <PlusCircleIcon
      v-if="!week.id"
      class="invisible col-span-2 col-start-1 row-span-2 row-start-1 m-auto h-[30%] animate-pulse group-hover:visible group-hover:scale-[115%]"
    />
    <div
      v-if="week.id"
      class="col-start-1 row-span-2 row-start-1 w-[24px] rounded-l-lg bg-primary-2"
    />
    <h4
      class="row-start-1 m-auto px-2 pt-2"
      :class="week.id ? 'col-start-2' : 'col-span-2 col-start-1'"
    >
      {{ `${t('menu.week')} #${week.calendarWeek}` }}
    </h4>
    <h5
      class="row-start-2 m-auto px-2 pb-4 pt-2"
      :class="week.id ? 'col-start-2' : 'col-span-2 col-start-1'"
    >
      {{ `${dateRange[0]} - ${dateRange[1]}` }}
    </h5>
  </div>
</template>

<script setup lang="ts">
import { type Week } from '@/stores/weeksStore';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useWeeks } from '@/stores/weeksStore';
import { PlusCircleIcon } from '@heroicons/vue/outline';
import { useRouter } from 'vue-router';

const { getDateRangeOfWeek, createEmptyWeek } = useWeeks();
const { t, locale } = useI18n();
const router = useRouter();

const props = defineProps<{
  week: Week;
}>();

const dateRange = computed(() => {
  return getDateRangeOfWeek(props.week.calendarWeek, props.week.year).map((date) =>
    date.toLocaleDateString(locale.value, { day: 'numeric', month: 'numeric' })
  );
});

async function handleClick() {
  if (props.week.id !== null && props.week.id !== undefined) {
    router.push({ name: 'Menu', params: { week: props.week.id } });
  } else {
    const response = await createEmptyWeek(props.week.year, props.week.calendarWeek);

    if (typeof response === 'number') {
      router.push({ name: 'Menu', params: { week: response, create: 'create' } });
    }
  }
}
</script>
