<template>
  <tbody
    ref="tableBody"
    class="z-1 grow-1 shrink-1 scrollbar-styling block basis-auto overflow-y-auto overflow-x-hidden rounded-b-lg bg-white"
  >
    <ParticipantsTableSlot
      v-for="(participants, slot) in participationsState.data"
      :key="slot"
      :slot-name="slot.toString()"
      :participants="
        // @ts-ignore
        convertToIBookedData(participants)
      "
      :meals="mealsWithVariations"
    />
  </tbody>
</template>

<script setup lang="ts">
import { IBookedData, getShowParticipations } from '@/api/getShowParticipations';
import ParticipantsTableSlot from './ParticipantsTableSlot.vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Dictionary } from '@/types/types';

const { participationsState, getMealsWithVariations, loadedState } = getShowParticipations();

const tableBody = ref<HTMLTableSectionElement | null>(null);
const scrollDirectionDown = ref(true);

const INTERVAL_DELAY = 16;
const SCROLL_AMOUNT = 1;
const TIMEOUT_AFTER_SCROLLING = 3000;

let scrollProcessId: number;
let scrollingActive = true;
let time: number;

const mealsWithVariations = computed(() => {
  if (loadedState.loaded === true) {
    return getMealsWithVariations();
  } else {
    return [];
  }
});

onMounted(() => {
  time = Date.now();
  scrollProcessId = window.setInterval(() => autoScroll(tableBody.value), INTERVAL_DELAY);
});

onUnmounted(() => {
  if (scrollProcessId !== undefined && scrollProcessId !== null) {
    clearInterval(scrollProcessId);
  }
});

/**
 * Scrolls the passed in element in the direction indicated by scrollDirection
 * @param element HTMLSectionElement to scroll
 */
function autoScroll(element: HTMLTableSectionElement | null) {
  const timeSinceLastScroll = Date.now() - time;
  if (scrollingActive === true && element !== null && element !== undefined) {
    setScrollDirection(element);
    element.scrollBy({
      top: scrollDirectionDown.value ? scrollAmount(timeSinceLastScroll) : -scrollAmount(timeSinceLastScroll)
    });
  }
  time = Date.now();
}

/**
 * Sets the scrollDirectionDown value depending on the current scroll position.
 * If the direction changes the sleep fuunction is called
 * @param element element that is scrolled
 */
function setScrollDirection(element: HTMLTableSectionElement | null) {
  const cachedScrollDirection = scrollDirectionDown.value;
  if (element !== null && element !== undefined && scrollDirectionDown.value === true) {
    scrollDirectionDown.value = element.scrollTop + element.clientHeight < element.scrollHeight;
  } else if (element !== null && element !== undefined && scrollDirectionDown.value === false) {
    scrollDirectionDown.value = element.scrollTop === 0;
  }

  if (cachedScrollDirection !== scrollDirectionDown.value) {
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
  setTimeout(() => (scrollingActive = true), ms);
}

/**
 * Computes the amount to scroll the table by in pixel
 * @param timeSinceLastScroll
 */
function scrollAmount(timeSinceLastScroll: number) {
  return timeSinceLastScroll * (SCROLL_AMOUNT / INTERVAL_DELAY);
}

/**
 * Workaround for a typescript linting problem where Dictionary<IBookedData>
 * is not recognized
 * @param participant The dictionary from the v-for, found in participationsState.data
 */
function convertToIBookedData(participant: Dictionary<IBookedData>): Dictionary<IBookedData> {
  return participant;
}

// expose functions for testing
if (process.env.NODE_ENV === 'TEST') {
  defineExpose({ scrollAmount, setScrollDirection, scrollDirectionDown, mealsWithVariations });
}
</script>

<style scoped>
.scrollbar-styling {
  scrollbar-width: none;
}

.scrollbar-styling::-webkit-scrollbar {
  display: none;
}
</style>
