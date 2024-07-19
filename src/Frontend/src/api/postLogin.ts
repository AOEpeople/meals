import useApi from '@/api/api';

export async function postLogin(username: string, password: string) {
    const { request, response, error } = useApi(
        'POST',
        'api/login',
        'application/json',
        JSON.stringify({ username: username, password: password })
    );

    await request();

    return { error, response };
}
