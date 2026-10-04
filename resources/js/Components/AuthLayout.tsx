import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

export default function AuthLayout({ title, children }: { title: string; children: ReactNode }) {
    return (
        <>
            <Head title={`${title} | RoomConnect`} />
            <main className="min-h-screen bg-slate-50 px-4 py-8 text-slate-950 sm:px-6">
                <div className="mx-auto max-w-md">
                    <Link href="/" className="text-lg font-black tracking-tight text-blue-700">RoomConnect</Link>
                    <div className="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <h1 className="text-2xl font-black">{title}</h1>
                        {children}
                    </div>
                </div>
            </main>
        </>
    );
}
