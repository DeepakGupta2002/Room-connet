import { Head, Link } from '@inertiajs/react';

export default function Home() {
    return (
        <>
            <Head title="Find Rooms Without Brokers" />

            <main className="min-h-screen bg-slate-50 text-slate-950">
                <section className="mx-auto flex min-h-screen max-w-6xl flex-col justify-center gap-10 px-6 py-12 lg:flex-row lg:items-center lg:px-12">
                    <div className="max-w-2xl">
                        <span className="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                            100% broker-free room discovery
                        </span>
                        <h1 className="mt-5 text-4xl font-black tracking-tight sm:text-6xl">
                            Find your next room directly from the owner.
                        </h1>
                        <p className="mt-5 max-w-xl text-lg leading-8 text-slate-600">
                            Browse rooms freely, verify the listing context, and unlock the owner contact only when you are ready.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <Link href="/search" className="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                Find rooms
                            </Link>
                            <Link href="/post-room" className="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700 transition hover:border-blue-400 hover:text-blue-700">
                                Post a room
                            </Link>
                        </div>
                    </div>

                    <div className="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-blue-100/50">
                        <div className="rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white">
                            <p className="text-sm font-medium text-blue-100">Start searching</p>
                            <h2 className="mt-2 text-2xl font-bold">Rooms near you</h2>
                            <div className="mt-6 rounded-xl bg-white/15 p-4 text-sm text-blue-50">
                                Approximate locations are public. Owner contact stays protected until OTP verification and unlock.
                            </div>
                        </div>
                        <div className="mt-5 grid grid-cols-2 gap-3 text-sm font-medium text-slate-700">
                            <div className="rounded-xl bg-slate-100 p-4">Phone verified</div>
                            <div className="rounded-xl bg-emerald-50 p-4 text-emerald-700">Owner contact</div>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
