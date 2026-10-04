import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';

export default function Register() {
    const form = useForm({ name: '', email: '', phone: '', latitude: '', longitude: '' });
    function submit(event: FormEvent) {
        event.preventDefault();
        const send = () => form.post('/auth/register');
        if (!navigator.geolocation) return send();
        navigator.geolocation.getCurrentPosition((position) => { form.setData('latitude', position.coords.latitude.toString()); form.setData('longitude', position.coords.longitude.toString()); setTimeout(send, 0); }, send, { enableHighAccuracy: false, timeout: 5000, maximumAge: 300000 });
    }
    return <AuthLayout title="Create your account"><p className="mt-2 text-sm leading-6 text-slate-500">No password required. Create a secure account with an email OTP.</p><form onSubmit={submit} className="mt-6 space-y-4"><input required value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Full name" className="field" /><input required type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} placeholder="Email address" className="field" /><input required inputMode="numeric" pattern="[0-9]{10}" maxLength={10} value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value.replace(/\D/g, '').slice(0, 10))} placeholder="10-digit mobile number" className="field" /><p className="text-xs leading-5 text-slate-500">Allow location access to save your approximate latitude and longitude with the account.</p>{Object.keys(form.errors).length > 0 && <div className="rounded-xl bg-red-50 p-3 text-sm leading-6 text-red-700">{Object.values(form.errors).join(' ')}</div>}<button disabled={form.processing} className="button">{form.processing ? 'Sending OTP…' : 'Continue with email OTP'}</button></form><p className="mt-6 text-center text-sm text-slate-500">Already registered? <Link href="/login" className="font-semibold text-blue-700">Login with OTP</Link></p></AuthLayout>;
}
