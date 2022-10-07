<template>
  <div class="flex flex-row gap-4 justify-around w-auto xl:grid-cols-6">
    <div class="items-center self-center basis-10/12 xl:col-span-5">
      <div class="self-center break-all sm:break-words">
        <span class="text-primary uppercase tracking-[1px] text-note font-bold">
          {{ title }}
          <span v-if="meal.isNew" class="w-[36px] h-[17px] bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]">
            {{ t('dashboard.new') }}
          </span>
        </span><br>
        <p v-if="description !== ''" class="m-0 font-light description text-primary">{{ description }}</p>
      </div>
    </div>
    <div class="flex flex-none justify-end items-center basis-2/12 text-align-last">
      <div :class="
        [meal.limit > 9 ? 'w-[65px]' : 'w-[46px]', 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] bg-primary']
      ">
        <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 ml-[7px] my-auto"/>
        <span class="text-white h-4 w-[15px] self-center leading-4 font-bold text-[11px] my-0.5 mr-[7px] tracking-[1.5px]">
          {{ meal.participations + [meal.limit > 0 ? '/' + meal.limit : ''] }}
        </span>
      </div>
      <GuestCheckbox :meal="meal"/>
    </div>
  </div>
</template>

<script setup>
import {computed} from "vue";
import Icons from "@/components/misc/Icons.vue";
import { useI18n } from "vue-i18n";
import GuestCheckbox from '@/components/guest/GuestCheckbox.vue'

const props = defineProps(['meal'])
const emit = defineEmits(['processMeal'])
const { t, locale } = useI18n();

let title = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.title.en : props.meal.title.de)
let description = ''
if(props.meal.description !== null) {
  description = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.description.en : props.meal.description.de);
}

function processMeal(slug) {
  console.log(slug)
  emit('processMeal', slug)
}

</script>

<style scoped>

</style>