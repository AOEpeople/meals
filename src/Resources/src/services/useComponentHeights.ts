import { computed, onMounted, onUnmounted, reactive, readonly, ref } from "vue";

interface IComponentHeightState {
  navBarHeight: number,
  tableHeadHeight: number,
  mealListHeight: number,
  mealOverviewHeight: number,
  screenHeight: number
}

const listenerActive = ref(false);

const windowWidth = ref(0);

const componentHeightState = reactive<IComponentHeightState>({
  navBarHeight: 0,
  screenHeight: 0,
  tableHeadHeight: 0,
  mealListHeight: 0,
  mealOverviewHeight: 0
});

const maxTableHeight = computed(() => {
  return componentHeightState.screenHeight - (componentHeightState.navBarHeight + componentHeightState.tableHeadHeight + componentHeightState.mealListHeight + componentHeightState.mealOverviewHeight);
});

function getMarginHeightByElementId(elementId: string) {
  const element = document.getElementById(elementId);
  if(element) {
    const computedStyle = window.getComputedStyle(element);
    return parseInt(computedStyle.marginTop, 10) + parseInt(computedStyle.marginBottom, 10);
  } else {
    return 0;
  }
}

function setWindowHeight() {
  componentHeightState.screenHeight = window.innerHeight;
  windowWidth.value = window.innerWidth;
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

  function setNavBarHeight(height: number, elementId: string) {
    componentHeightState.navBarHeight = height + getMarginHeightByElementId(elementId);
  }

  function setTableHeadHight(height: number, elementId: string) {
    componentHeightState.tableHeadHeight = height + getMarginHeightByElementId(elementId);
  }

  function setMealListHight(height: number, elementId: string) {
    componentHeightState.mealListHeight = height + getMarginHeightByElementId(elementId);
  }

  function setMealOverviewHeight(height: number, elementId: string) {
    componentHeightState.mealOverviewHeight = height + getMarginHeightByElementId(elementId);
  }

  return {
    maxTableHeight,
    windowWidth: readonly(windowWidth),
    setNavBarHeight,
    setTableHeadHight,
    setMealListHight,
    setMealOverviewHeight
  }
}