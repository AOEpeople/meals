<template>
  <tbody
    ref="tableBody"
    class="grow-1 shrink-1 block basis-auto overflow-y-auto overflow-x-hidden"
  >
    <ParticipantsTableSlot
      v-for="(participants, slot) in participationsState.data"
      :key="slot"
      :slot-name="slot.toString()"
      :participants="participants"
      :meals="mealsWithVariations"
    />
  </tbody>
</template>

<script setup lang="ts">
import { getShowParticipations } from '@/api/getShowParticipations';
import ParticipantsTableSlot from './ParticipantsTableSlot.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const { participationsState, getMealsWithVariations, loadedState } = getShowParticipations();

const tableBody = ref<HTMLTableSectionElement | null>(null);
const scrollDirectionDown = ref(true);

const INTERVAL_DELAY = 16;
const SCROLL_AMOUNT = 1;
const TIMEOUT_AFTER_SCROLLING = 3000;

let scrollProcessId:number;
let scrollingActive = true;

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getMealsWithVariations();
  } else {
    return [];
  }
});

onMounted(() => {
  scrollProcessId = window.setInterval(() => autoScroll(tableBody.value), INTERVAL_DELAY);
});

onUnmounted(() => {
  if(scrollProcessId) {
    clearInterval(scrollProcessId);
  }
})

/**
 * Scrolls the passed in element in the direction indicated by scrollDirection
 * @param element HTMLSectionElement to scroll
 */
function autoScroll(element: HTMLTableSectionElement | null) {
  if(scrollingActive && element) {
    setScrollDirection(element)
    element.scrollBy({
      top: scrollDirectionDown.value ? SCROLL_AMOUNT : -SCROLL_AMOUNT,
      behavior: 'smooth'
    });
  }
}

/**
 * Sets the scrollDirectionDown value depending on the current scroll position.
 * If the direction changes the sleep fuunction is called
 * @param element element that is scrolled
 */
function setScrollDirection(element: HTMLTableSectionElement | null) {
  const cachedScrollDirection = scrollDirectionDown.value;
  if(element && scrollDirectionDown.value) {
    scrollDirectionDown.value = (element.scrollTop + element.clientHeight) < element.scrollHeight;
  } else if(element && !scrollDirectionDown.value) {
    scrollDirectionDown.value = element.scrollTop === 0;
  }

  if(cachedScrollDirection !== scrollDirectionDown.value) {
    sleep(TIMEOUT_AFTER_SCROLLING);
  }

}

/**
 * Sets scrollingActive to false, then waits a passed in number of milliseconds,
 * then sets scrollingActive to true again
 * @param ms
 */
function sleep(ms: number) {
  scrollingActive = false;
  setTimeout(() => scrollingActive = true, ms);
}
</script>

<style scoped>

</style>