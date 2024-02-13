import { computed, onMounted, reactive, readonly, ref } from 'vue';

interface IComponentHeightState {
    navBarHeight: number;
    tableHeadHeight: number;
    mealListHeight: number;
    mealOverviewHeight: number;
    screenHeight: number;
}

const MIN_TABLE_HEIGHT = 250;

const listenerActive = ref(false);

const windowWidth = ref(0);

/**
 * State for the different heights of components used in the ParticipantsList view
 */
const componentHeightState = reactive<IComponentHeightState>({
    navBarHeight: 0,
    screenHeight: 0,
    tableHeadHeight: 0,
    mealListHeight: 0,
    mealOverviewHeight: 0
});

/**
 * Computed maximum possible height of the table body of the ParticipantsTableBody component.
 */
const maxTableHeight = computed(() => {
    const height =
        componentHeightState.screenHeight -
        (componentHeightState.navBarHeight +
            componentHeightState.tableHeadHeight +
            componentHeightState.mealListHeight +
            componentHeightState.mealOverviewHeight);
    if (height < MIN_TABLE_HEIGHT) {
        return MIN_TABLE_HEIGHT;
    }

    return height;
});

/**
 * Computed maximum possible height of the NoParticipations-component.
 */
const maxNoParticipationsHeight = computed(() => {
    const height =
        componentHeightState.screenHeight -
        (componentHeightState.navBarHeight + componentHeightState.mealOverviewHeight);
    if (height < MIN_TABLE_HEIGHT) {
        return MIN_TABLE_HEIGHT;
    }

    return height;
});

/**
 * Computes the sum of margin-bottom and margin-top of an HTMLELement.
 * @param elementId ID of the HTMLElement
 * @returns height in pixel
 */
function getMarginHeightByElementId(elementId: string) {
    const element = document.getElementById(elementId);
    if (element !== null && element !== undefined) {
        const computedStyle = window.getComputedStyle(element);
        return parseInt(computedStyle.marginTop, 10) + parseInt(computedStyle.marginBottom, 10);
    } else {
        return 0;
    }
}

/**
 * Sets screenHeight in the componentHeightState and the value of windowWidth to the current window dimensions
 */
function setWindowHeight() {
    componentHeightState.screenHeight = window.innerHeight;
    windowWidth.value = window.innerWidth;
}

/**
 * Composable function to get the maximum possible height of the table body of the ParticipantsTableBody component.
 * Initiates an eventlistener for the 'resize'-event when first Mounted.
 */
export function useComponentHeights() {
    onMounted(() => {
        setWindowHeight();
    });

    function addWindowHeightListener() {
        if (listenerActive.value === false) {
            listenerActive.value = true;
            window.addEventListener('resize', setWindowHeight);
        }
    }

    function removeWindowHeightListener() {
        if (listenerActive.value === true) {
            listenerActive.value = false;
            window.removeEventListener('resize', setWindowHeight);
        }
    }

    /**
     * Sets the height (offsetHeight + marginHeight) of the NavBar in the componentHeightState
     * @param height offsetHeight of the Element
     * @param elementId ID of the HTMLElement from which the height was passed in
     */
    function setNavBarHeight(height: number, elementId: string) {
        componentHeightState.navBarHeight = height + getMarginHeightByElementId(elementId);
    }

    /**
     * Sets the height (offsetHeight + marginHeight) of the TableHead in the componentHeightState
     * @param height offsetHeight of the Element
     * @param elementId ID of the HTMLElement from which the height was passed in
     */
    function setTableHeadHight(height: number, elementId: string) {
        componentHeightState.tableHeadHeight = height + getMarginHeightByElementId(elementId);
    }

    /**
     * Sets the height (offsetHeight + marginHeight) of the MealList in the componentHeightState
     * @param height offsetHeight of the Element
     * @param elementId ID of the HTMLElement from which the height was passed in
     */
    function setMealListHight(height: number, elementId: string) {
        componentHeightState.mealListHeight = height + getMarginHeightByElementId(elementId);
    }

    /**
     * Sets the height (offsetHeight + marginHeight) of the MealOverview in the componentHeightState
     * @param height offsetHeight of the Element
     * @param elementId ID of the HTMLElement from which the height was passed in
     */
    function setMealOverviewHeight(height: number, elementId: string) {
        componentHeightState.mealOverviewHeight = height + getMarginHeightByElementId(elementId);
    }

    return {
        maxTableHeight,
        windowWidth: readonly(windowWidth),
        maxNoParticipationsHeight: readonly(maxNoParticipationsHeight),
        setNavBarHeight,
        setTableHeadHight,
        setMealListHight,
        setMealOverviewHeight,
        addWindowHeightListener,
        removeWindowHeightListener
    };
}
