import { Ref, onBeforeUnmount, ref } from "vue";

export default function useDetectClickOutside(componentRef: Ref<HTMLElement | null>, callback: () => void) {
    
    const listenerActive = ref(false);

    if (componentRef === null || componentRef === undefined) {
        return;
    }

    function addListener() {
        if (listenerActive.value === true) {
            return;
        }
        document.addEventListener('click', listener);
        listenerActive.value = true;
    }

    function removeListener() {
        if (listenerActive.value === false) {
            return;
        }
        document.removeEventListener('click', listener);
        listenerActive.value = false;
    }

    function listener<E extends MouseEvent | PointerEvent >(event: E) {

        if (event.defaultPrevented === true) {
            return;
        }
        const target = (event.composedPath?.()?.[0] || event.target) as HTMLElement | null;

        if (target === null || target === undefined || target.getRootNode().contains(target) === false || componentRef.value === null || componentRef.value === undefined) {
            return;
        }
        const node = (componentRef.value as { $el?: HTMLElement }).$el ?? componentRef.value;

        if (node?.contains(target) === true) {
            return;
        } else if (event.composed === true && event.composedPath().includes(node as EventTarget) === true) {
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