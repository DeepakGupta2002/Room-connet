import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AuthLayout from '../../Components/AuthLayout';

export default function VerifyOtp({ flow, email }: { flow: 'register' | 'login'; email: string }) {
    const form = useForm({ flow, otp: '' });
    const resendForm = useForm({ flow });
    const { flash } = usePage<{ flash?: { status?: string } }>().props;
    function verify(event: FormEvent) { event.preventDefault(); form.post('/auth/otp/verify'); }
    function resend() { resendForm.post('/auth/otp/resend'); }
    const maskedEmail = email.replace(/^(.{2})(.*)(@.*)$/, '$1•••$3');
    return <><Head title="Verify OTP" /><AuthLayout title="Verify your OTP"><p className="mt-2 text-sm leading-6 text-slate-500">OTP <strong>{maskedEmail}</strong> par bheja gaya hai.</p>{flash?.status && <p className="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-700">{flash.status}</p>}<form onSubmit={verify} className="mt-6 space-y-4"><input required autoFocus inputMode="numeric" pattern="[0-9]{6}" maxLength={6} value={form.data.otp} onChange={(e) => form.setData('otp', e.target.value.replace(/\D/g, '').slice(0, 6))} placeholder="6-digit OTP" className="field text-center text-xl tracking-[0.35em]" />{Object.keys(form.errors).length > 0 && <div className="rounded-xl bg-red-50 p-3 text-sm leading-6 text-red-700">{Object.values(form.errors).join(' ')}</div>}<button disabled={form.processing} className="button">{form.processing ? 'Verifying…' : 'Verify OTP'}</button></form><button type="button" onClick={resend} disabled={resendForm.processing} className="mt-5 w-full text-sm font-bold text-blue-700">{resendForm.processing ? 'Sending…' : 'Resend OTP'}</button><Link href={flow === 'register' ? '/register' : '/login'} className="mt-4 block text-center text-sm font-semibold text-slate-500">← Back to edit email</Link></AuthLayout></>;
}
