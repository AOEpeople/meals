import axios from "axios";
import { ref } from "vue";

const instance = axios.create({
    baseURL: window.location.origin,
    timeout: 5000,
})

export default function useApi<T>(method: string, url: string, contentType = 'application/json', data?: string){
    const response = ref<T>()
    const error = ref<boolean>(false)
    const request = async () => {
        await instance({
            method: method,
            url: url,
            data: data,
            headers: { 'content-type': contentType },
        })
            .then((res) => {
                response.value = res.data
            })
            .catch((err: unknown) => {
                error.value = true;
                // @ts-expect-error bla
                console.log(err.response);
                if (axios.isAxiosError(err))  {
                  // Access to config, request, and response
                  console.log('Err.message: ', err.message);
                  console.log('response error: ', err.toJSON());
                } else if (err instanceof Error) {
                  // Just a stock error
                  console.log('Stock err.msg: ', err.message);
                }
            })
    }
    return { response, request, error }
}