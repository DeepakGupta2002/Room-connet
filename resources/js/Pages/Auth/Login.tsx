import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';

export default function Login() {
    const form = useForm({ email: '', password: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/auth/login');
    }

    return (
        <AuthLayout title="Login to RoomConnect">
            <p className="mt-2 text-sm leading-6 text-slate-500">Use your verified email to unlock owner contacts securely.</p>
            <form onSubmit={submit} className="mt-6 space-y-4">
                <input required type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} placeholder="Email address" className="field" />
                <input required type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} placeholder="Password" className="field" />
                {form.errors.email && <p className="text-sm text-red-600">{form.errors.email}</p>}
                <button disabled={form.processing} className="button">{form.processing ? 'Logging in…' : 'Login'}</button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-500">New here? <Link href="/register" className="font-semibold text-blue-700">Create account</Link></p>
        </AuthLayout>
    );
}
