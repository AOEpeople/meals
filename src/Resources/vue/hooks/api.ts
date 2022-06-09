import axios from "axios";
import { ref } from "vue";

const instance = axios.create({
    baseURL: 'https://meals.test/',
    timeout: 1000,
    headers: {'content-type': 'application/json'}
});

export default function useApi<T>(method: string, url: string, data?: JSON){
    const response = ref<T>();
    const request = async () => {
        await instance({
            method: method,
            url: url,
            data: data,
        })
            .then((res) => response.value = res.data)
            .catch((error) => console.log(error));
    };
    return { response, request };
}