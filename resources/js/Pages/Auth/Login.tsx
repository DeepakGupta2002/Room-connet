import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';

export default function Login() {
    const form = useForm({ identifier: '' });
    function submit(event: FormEvent) { event.preventDefault(); form.post('/auth/login'); }
    return <AuthLayout title="Login to RoomConnect"><p className="mt-2 text-sm leading-6 text-slate-500">Email ya registered phone number enter karein. Password ki zaroorat nahi.</p><form onSubmit={submit} className="mt-6 space-y-4"><input required value={form.data.identifier} onChange={(e) => form.setData('identifier', e.target.value)} placeholder="Email or mobile number" className="field" />{Object.keys(form.errors).length > 0 && <div className="rounded-xl bg-red-50 p-3 text-sm leading-6 text-red-700">{Object.values(form.errors).join(' ')}</div>}<button disabled={form.processing} className="button">{form.processing ? 'Sending OTP…' : 'Continue with OTP'}</button></form><p className="mt-6 text-center text-sm text-slate-500">New here? <Link href="/register" className="font-semibold text-blue-700">Create account</Link></p></AuthLayout>;
}
