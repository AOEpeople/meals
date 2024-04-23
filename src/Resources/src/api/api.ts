import axios from 'axios';
import { ref } from 'vue';

const instance = axios.create({
    baseURL: window.location.origin,
    timeout: 5000
});

export default function useApi<T>(method: string, url: string, contentType = 'application/json', data?: string) {
    const response = ref<T>();
    const error = ref<boolean>(false);
    const request = async () => {
        await instance({
            method: method,
            url: url,
            data: data,
            headers: { 'content-type': contentType }
        })
            .then((res) => {
                response.value = res.data as T;
            })
            .catch((err) => {
                error.value = true;
                if (
                    err.response !== null &&
                    err.response !== undefined &&
                    err.response.data !== null &&
                    err.response.data !== undefined
                ) {
                    response.value = err.response.data;
                }
            });
    };
    return { response, request, error };
}
