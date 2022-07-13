<template>
  <div class="mb-1">
    <span class="text-primary uppercase tracking-[1px] text-note font-bold">{{ parentTitle }}</span><br>
  </div>
  <div v-for="variation in meal.variations" class="flex flex-row w-auto gap-4 mb-1.5 last:mb-0 xl:grid-cols-6 justify-around">
    <div class="items-center self-center basis-10/12 xl:col-span-5">
      <div class="self-center">
        <p class="m-0 font-light break-words description text-primary">
          {{ locale.substring(0, 2) === 'en' ? variation.title.en : variation.title.de }}
          <span v-if="variation.isNew" class="w-[36px] h-[17px] uppercase font-bold bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]">
            {{ t('dashboard.new') }}
          </span>
        </p>
      </div>
    </div>
    <div class="flex flex-none justify-end items-center basis-2/12 text-align-last">
      <div :class="
        [variation.limit > 9 ? 'w-[65px]' : 'w-[46px]',
        [disabled || variation.reachedLimit ? 'bg-[#80909F]' : 'bg-primary-4', 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px]']]
      ">
        <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 my-[7px] mx-1"/>
        <span class="text-white h-4 w-[15px] self-center leading-4 font-bold text-[11px] my-0.5 mr-[7px] tracking-[1.5px]">
          {{ variation.participations }}
        </span>
      </div>
      <Checkbox :mealData="variation" :disabled="disabled" :dayId="dayId"/>
    </div>
  </div>
</template>

<script setup>
import Icons from '@/components/misc/Icons.vue'
import Checkbox from '@/components/dashboard/Checkbox.vue'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps([
  'meal',
  'disabled',
  'dayId'
])

let parentTitle = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.title.en : props.meal.title.de)

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>