<template>
  <div class="flex flex-row w-auto gap-4 xl:grid-cols-6 justify-around">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center sm:break-words break-all">
        <span class="text-primary uppercase tracking-[1px] text-note font-bold">{{ title }}</span><br>
        <p v-if="description !== ''" class="description m-0 font-light text-primary">{{ description }}</p>
      </div>
    </div>
    <div class="flex basis-2/12 flex-none justify-end items-center text-align-last">
      <div :class="
        [meal.limit > 9 ? 'w-[65px]' : 'w-[46px]',
        [disabled ? 'bg-[#80909F]' : 'bg-primary-4', 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px]']]
      ">
        <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 ml-[7px] my-auto"/>
        <span class="text-white h-4 w-[15px] self-center leading-4 font-bold text-[11px] my-0.5 mr-[7px] tracking-[1.5px]">{{ meal.participations + [meal.limit > 0 ? '/' + meal.limit : ''] }}</span>
      </div>
      <Checkbox :mealData="meal" :disabled="disabled"/>
    </div>
  </div>
</template>

<script setup>
import Icons from "@/components/misc/Icons.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import { useI18n } from "vue-i18n";
import {computed} from "vue";

const props = defineProps([
    'meal',
    'disabled'
]);
const { t, locale } = useI18n();

let title       = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.title.en       : props.meal.title.de);
let description = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.description.en : props.meal.description.de);

let disabled = props.disabled || props.meal.reachedLimit

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
.check {
  display: block;
  width: 22px;
  height: 20px;
  position: relative;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.check input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  border-radius: 6px;
  border-color: #ababab;
  border-width: 0.5px;
  height: 20px;
  width: 22px;
  background-color: #fafafa;
}
.check:hover input ~ .checkmark {
  background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.check input:checked ~ .checkmark {
  background-color: #518AD5;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.check input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.check .checkmark:after {
  left: 8px;
  top: 5px;
  width: 5px;
  height: 10px;
  border: 2px solid #FFFFFF;
  border-radius: 2px;
  transform: matrix(-1, 0, 0, 1, 0, 0) rotateZ(45deg);
}
</style>