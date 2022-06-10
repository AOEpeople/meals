<template>
  <div class="flex flex-row w-auto xl:grid-cols-6 justify-around">
    <div class="basis-10/12 gap-8 items-center self-center xl:col-span-5">
      <div class="self-center break-words">
        <span class="text-primary uppercase tracking-[1px] text-note font-bold">{{ props.data.name }}</span><br>
        <p v-if="props.data.description !== ''" class="description m-0 font-light text-primary">{{ props.data.description }}</p>
      </div>
    </div>
    <div class="flex basis-2/12 flex-none justify-end items-center text-align-last">
      <div class="grid grid-cols-2 content-center rounded-md w-[46px] h-[20px] mr-[15px] bg-primary-4">
        <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 my-[7px] mx-1"/>
        <span class="text-white h-4 w-[15px] self-center leading-4 font-bold text-[11px] my-0.5 mr-[7px] tracking-[1.5px]">{{ props.data.participations }}</span>
      </div>
      <label class="check">
        <input type="checkbox">
        <span class="checkmark"></span>
      </label>
    </div>
  </div>
</template>

<script setup>
import Icons from "@/components/Icons.vue";

const props = defineProps([
  'data',
]);

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