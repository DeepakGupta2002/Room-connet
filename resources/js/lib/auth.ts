export type ApiError = { message?: string; errors?: Record<string, string[]> };

export async function postJson<T>(url: string, payload?: Record<string, unknown>): Promise<T> {
    const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        credentials: 'same-origin',
        body: payload ? JSON.stringify(payload) : undefined,
    });

    const data = (await response.json().catch(() => ({}))) as T & ApiError;
    if (!response.ok) throw data;
    return data;
}
