import { onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { getShowParticipations } from "@/api/getShowParticipations";

interface IComponentHeightState {
  componentsHeight: number,
  screenHeight: number
}

interface IElementIdsState {
  naviBarId: string,
  tableHeaderId: string,
  mealsSummaryId: string,
  mealsListId: string
}

const { loadedState } = getShowParticipations();

const maxTableHeight = ref(0);

const listenerActive = ref(false);

const summaryUpdated = ref(0);

const componentHeightState = reactive<IComponentHeightState>({
  componentsHeight: 0,
  screenHeight: 0
});

const elementIdsState = reactive<IElementIdsState>({
  naviBarId: "",
  tableHeaderId: "",
  mealsSummaryId: "",
  mealsListId: ""
});

watch(
  elementIdsState,
  () => computeCombinedComponentHeight(),
  { deep: true }
);

watch(
  () => componentHeightState.componentsHeight,
  () => computeMaxTableHeight()
);

watch(
  () => componentHeightState.screenHeight,
  () => computeMaxTableHeight()
);

watch(
  () => loadedState.loaded,
  () => computeCombinedComponentHeight()
);

watch(
  () => summaryUpdated.value,
  () => computeCombinedComponentHeight()
);

function computeMaxTableHeight() {
  maxTableHeight.value = componentHeightState.screenHeight - componentHeightState.componentsHeight;
}

function getMarginHeightByElementId(elementId: string) {
  const element = document.getElementById(elementId);
  if(element) {
    const computedStyle = window.getComputedStyle(element);
    return parseInt(computedStyle.marginTop, 10) + parseInt(computedStyle.marginBottom, 10);
  } else {
    return 0;
  }
}

function getOffsetHeightByElementId(elementId: string) {
  const element = document.getElementById(elementId);
  if(element) {
    return element.offsetHeight;
  } else {
    return 0;
  }
}

function computeCombinedComponentHeight() {
  componentHeightState.componentsHeight = 0;
  for(const elementId of Object.values(elementIdsState)) {
    if(elementId === "") {
      continue;
    }
    const margin = getMarginHeightByElementId(elementId);
    const height = getOffsetHeightByElementId(elementId);
    componentHeightState.componentsHeight += height + margin;
  }
}

function setWindowHeight() {
  componentHeightState.screenHeight = window.innerHeight;
}

export function useComponentHeights() {

  onMounted(() => {
    if (!listenerActive.value) {
      listenerActive.value = true;
      setWindowHeight();
      window.addEventListener('resize', setWindowHeight);
    }
  });

  onUnmounted(() => {
    if (listenerActive.value) {
      listenerActive.value = false;
      window.removeEventListener('resize', setWindowHeight);
    }
  });

  function setNaviBarId(elementId: string) {
    elementIdsState.naviBarId = elementId;
  }

  function setTableHeaderId(elementId: string) {
    elementIdsState.tableHeaderId = elementId;
  }

  function setMealsSummaryId(elementId: string) {
    elementIdsState.mealsSummaryId = elementId;
  }

  function setMealsListId(elementId: string) {
    elementIdsState.mealsListId = elementId;
  }

  function setSummaryUpdated() {
    summaryUpdated.value += 1;
  }

  return {
    maxTableHeight,
    setNaviBarId,
    setTableHeaderId,
    setMealsSummaryId,
    setMealsListId,
    setSummaryUpdated
  }
}