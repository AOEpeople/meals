import axios from "axios";
import { ref } from "vue";

const instance = axios.create({
    baseURL: process.env.baseURL,
    timeout: 1000,
});

export default function useApi<T>(method: string, url: string, contentType: string = 'application/json', data?: JSON){
    const response = ref<T>();
    const request = async () => {
        await instance({
            method: method,
            url: url,
            data: data,
            headers: {'content-type': contentType },
        })
            .then((res) => response.value = res.data)
            .catch((error) => console.log(error));
    };
    return { response, request };
}