import {Env, useEnvs} from "@/api/getEnvironmentals";

export default async function getEnv(): Promise<Env | undefined> {
    const rawEnvs = sessionStorage.getItem("ENV")
    if (rawEnvs !== null) {
        return JSON.parse(rawEnvs) as Env
    } else {
        const { env, error } = await useEnvs();
        if (error.value) {
            console.log("couldn't receive Environmentals")
            return undefined
        } else {
            return env
        }
    }
}