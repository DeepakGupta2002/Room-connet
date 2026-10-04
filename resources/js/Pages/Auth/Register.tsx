import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';

export default function Register() {
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/auth/register');
    }

    return (
        <AuthLayout title="Create your account">
            <p className="mt-2 text-sm leading-6 text-slate-500">Browse freely. Email verification is required before contact unlock.</p>
            <form onSubmit={submit} className="mt-6 space-y-4">
                <input required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Full name" className="field" />
                <input required type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} placeholder="Email address" className="field" />
                <input required type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} placeholder="Password (8+ characters)" className="field" />
                <input required type="password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} placeholder="Confirm password" className="field" />
                {form.errors.email && <p className="text-sm text-red-600">{form.errors.email}</p>}
                <button disabled={form.processing} className="button">{form.processing ? 'Creating…' : 'Create account'}</button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-500">Already registered? <Link href="/login" className="font-semibold text-blue-700">Login</Link></p>
        </AuthLayout>
    );
}
