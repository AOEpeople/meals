<template>
  <div class="flex mx-auto w-3/4 h-auto bg-white rounded day-shadow w-max-screen-aoe">
    <div class="flex justify-center w-[24px] bg-primary-2 rounded-l-[5px]">
      <div id="icon" class="relative left-[425%] bottom-[2%]">
        <Icons icon="guest" box="0 0 13 13" class="w-[13px] h-[13px] fill-white"/>
      </div>
      <div class="grid weekday min-w-[200px]">
        <div id="dayLabel" class="mb-1">
          <span class="uppercase align-top dayLabel">{{ t(props.day.name) }}</span>
        </div>
      </div>
    </div>
    <div class="flex flex-col flex-1">
      <MealData :data="props.day.meal1" class="py-[13px] mx-[15px] border-b-[0.7px]" />
      <MealData :data="props.day.meal2" class="py-[13px] mx-[15px] border-b-[0.7px]" />
      <MealData :data="props.day.kombi" class="py-[13px] mx-[15px]" />
    </div>
  </div>
</template>

<script setup>
import MealData from "@/components/dashboard/MealData.vue";
import Icons from "@/components/misc/Icons.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps([
    'day',
])

</script>

<style scoped>
.day-shadow {
  box-shadow: 0 4px 0 hsla(0,0%,100%,.46),0 15px 35px rgba(216,225,233,.8);
}
.weekday {
  align-items: center;
  position: relative;
  top: 50%;
  transform: translateY(-50%) rotate(-90deg);
}
.dayLabel {
  color: #fff;
  font-family: Roboto, Helvetica, Arial, sans-serif;
  font-size: 11px;
  font-weight: 700;
  line-height: 16px;
  letter-spacing: 1.5px;
}

#icon {
  align-self: self-end;
}

#dayLabel {
  text-align-last: center;
}

</style>