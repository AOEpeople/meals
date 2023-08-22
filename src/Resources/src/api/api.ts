import { IMessage } from "@/interfaces/IMessage";
import axios from "axios";
import { ref } from "vue";

const instance = axios.create({
    baseURL: window.location.origin,
    timeout: 5000,
})

// Time until request gets aborted in ms
const TIMEOUT = 5000;
const BASE_URL = window.location.origin;

export default function useApi<T>(method: string, url: string, contentType = 'application/json', data?: string){
    const response = ref<T>()
    const error = ref<boolean>(false)
    const request = async () => {
        await instance({
            method: method,
            url: url,
            data: data,
            headers: { 'content-type': contentType },
        }).then((res) => {
            response.value = res.data
        }).catch((err) => {
            error.value = true;
            if (err.response.data !== null && err.response.data !== undefined) {
                response.value = err.response.data;
            }
        });
    }
    return { response, request, error };
}

export function useApi2<T>(method: string, url: string, contentType = 'application/json', data?: string) {
    const response = ref<T>(null);
    const error = ref<boolean>(false);

    const request = async () => {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), TIMEOUT);

            const res = await fetch(BASE_URL + url, {
                method: method,
                headers: {
                    'Content-Type': contentType
                },
                signal: controller.signal,
                body: data

            });
            clearTimeout(timeoutId);

            if (res.ok === false) {
                error.value = true;
            }

            response.value = await res.json();

        } catch (err) {
            console.log(err);
            error.value = true;
        }
    }

    return { response, request, error };
}