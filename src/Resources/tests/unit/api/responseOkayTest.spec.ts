import { isResponseOkay } from "@/api/isResponseOkay";
import { ref } from "vue";

describe('Test isResponseOkay', () => {
    it('should return true if the response is defined, not null and there are no errors', () => {
        const response = ref({
            test: 'test'
        });
        const error = ref(false);
        expect(isResponseOkay(error, response)).toBeTruthy();
    });

    it('should return false if there is an error', () => {
        const response = ref({
            test: 'test'
        });
        const error = ref(true);
        expect(isResponseOkay(error, response)).toBeFalsy();
    });

    it('should return false if respose is null', () => {
        const response = ref(null);
        const error = ref(false);
        expect(isResponseOkay(error, response)).toBeFalsy();
    });

    it('should be false if response is undefined', () => {
        const response = ref(undefined);
        const error = ref(false);
        expect(isResponseOkay(error, response)).toBeFalsy();
    });

    it('should be false if respose is undefined and error is true', () => {
        const response = ref(undefined);
        const error = ref(true);
        expect(isResponseOkay(error, response)).toBeFalsy();
    });
});