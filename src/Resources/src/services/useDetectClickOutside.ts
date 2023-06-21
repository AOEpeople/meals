import { Ref, onBeforeUnmount, ref } from "vue";

const listenerActive = ref(false);


export default function useDetectClickOutside(componentRef: Ref<HTMLElement | null>, callback: () => void) {
    if (!componentRef) {
        return;
    }

    function addListener() {
        if (listenerActive.value) {
            return;
        }
        window.addEventListener('click', listener);
        listenerActive.value = true;
    }

    function removeListener() {
        if (!listenerActive.value) {
            return;
        }
        window.removeEventListener('click', listener);
        listenerActive.value = false;
    }

    function listener<E extends MouseEvent | PointerEvent >(event: E) {

        if (event.defaultPrevented) {
            return;
        }
        const target = (event.composedPath?.()?.[0] || event.target) as HTMLElement | null;

        if (!target || !target.getRootNode().contains(target) || !componentRef.value) {
            return;
        }
        const node = (componentRef.value as { $el?: HTMLElement }).$el ?? componentRef.value;

        if (node?.contains(target)) {
            return;
        } else if (event.composed && event.composedPath().includes(node as EventTarget)) {
            return;
        } else if (typeof callback === 'function') {
            removeListener();
            callback();
        }
    }

    addListener();

    onBeforeUnmount(() => {
        removeListener();
    });

    return { listener };
}