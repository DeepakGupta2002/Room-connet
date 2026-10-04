import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { FormEvent } from 'react';

type FormData = {
    listed_by_role: 'tenant' | 'owner';
    title: string;
    description: string;
    rent_amount: string;
    security_deposit: string;
    maintenance_charge: string;
    room_type: string;
    available_from: string;
    leaving_date: string;
    city: string;
    area: string;
    locality: string;
    pincode: string;
    approximate_address: string;
    latitude: string;
    longitude: string;
    owner_name: string;
    owner_phone: string;
    images: File[];
};

export default function PostRoom() {
    const { flash, googleMapsKey } = usePage<{ flash?: { status?: string }; googleMapsKey?: string }>().props;
    const addressRef = useRef<HTMLInputElement>(null);
    const mapRef = useRef<HTMLDivElement>(null);
    const [mapsReady, setMapsReady] = useState(false);
    const form = useForm<FormData>({
        listed_by_role: 'tenant', title: '', description: '', rent_amount: '', security_deposit: '',
        maintenance_charge: '', room_type: 'private_room', available_from: '', leaving_date: '',
        city: '', area: '', locality: '', pincode: '', approximate_address: '', latitude: '', longitude: '', owner_name: '', owner_phone: '', images: [],
    });

    useEffect(() => {
        if (!googleMapsKey || document.querySelector('script[data-roomconnect-maps]')) {
            if (googleMapsKey && window.google) setMapsReady(true);
            return;
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(googleMapsKey)}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.dataset.roomconnectMaps = 'true';
        script.onload = () => setMapsReady(true);
        document.head.appendChild(script);
    }, [googleMapsKey]);

    useEffect(() => {
        if (!mapsReady || !addressRef.current || !window.google) return;

        const autocomplete = new window.google.maps.places.Autocomplete(addressRef.current, { fields: ['address_components', 'formatted_address', 'geometry'] });
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            const location = place.geometry?.location;
            if (!location) return;

            const latitude = location.lat().toString();
            const longitude = location.lng().toString();
            form.setData('approximate_address', place.formatted_address ?? '');
            form.setData('latitude', latitude);
            form.setData('longitude', longitude);

            const components = place.address_components ?? [];
            const find = (type: string) => components.find((item: { types: string[] }) => item.types.includes(type))?.long_name ?? '';
            form.setData('city', find('locality') || find('administrative_area_level_2'));
            form.setData('area', find('sublocality_level_1') || find('neighborhood'));
            form.setData('pincode', find('postal_code'));

            if (mapRef.current) {
                const map = new window.google.maps.Map(mapRef.current, { center: { lat: location.lat(), lng: location.lng() }, zoom: 14, disableDefaultUI: true });
                new window.google.maps.Marker({ position: { lat: location.lat(), lng: location.lng() }, map });
            }
        });
    }, [mapsReady]);

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/post-room', { forceFormData: true });
    }

    const field = (name: keyof FormData, type = 'text') => (
        <input type={type} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} className="field" />
    );

    return (
        <><Head title="Post a Room" /><main className="min-h-screen bg-slate-50 px-4 py-6 text-slate-950 sm:px-8"><div className="mx-auto max-w-3xl"><Link href="/" className="font-black text-blue-700">RoomConnect</Link><h1 className="mt-8 text-3xl font-black">Post a room</h1><p className="mt-2 text-slate-500">Tenant ya owner apni listing add kar sakta hai. Owner approval MVP me optional hai.</p>{flash?.status && <div className="mt-5 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{flash.status}</div>}<form onSubmit={submit} className="mt-7 space-y-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <section><h2 className="font-bold">Listing identity</h2><div className="mt-4 grid gap-4 sm:grid-cols-2"><label className="text-sm font-medium">Listed by<select value={form.data.listed_by_role} onChange={(e) => form.setData('listed_by_role', e.target.value as FormData['listed_by_role'])} className="field mt-2"><option value="tenant">Tenant</option><option value="owner">Owner</option></select></label><label className="text-sm font-medium">Room type<select value={form.data.room_type} onChange={(e) => form.setData('room_type', e.target.value)} className="field mt-2"><option value="private_room">Private room</option><option value="shared_room">Shared room</option><option value="shared_flat">Shared flat</option><option value="studio">Studio</option><option value="1bhk">1 BHK</option><option value="2bhk">2 BHK</option><option value="3bhk">3 BHK</option></select></label></div><label className="mt-4 block text-sm font-medium">Title{field('title')}</label><label className="mt-4 block text-sm font-medium">Description<textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className="field mt-2 min-h-28" placeholder="Room, flatmates, rules, amenities..." /></label></section>
            <section><h2 className="font-bold">Rent and dates</h2><div className="mt-4 grid gap-4 sm:grid-cols-3"><label className="text-sm font-medium">Monthly rent{field('rent_amount', 'number')}</label><label className="text-sm font-medium">Deposit{field('security_deposit', 'number')}</label><label className="text-sm font-medium">Maintenance{field('maintenance_charge', 'number')}</label><label className="text-sm font-medium">Available from{field('available_from', 'date')}</label><label className="text-sm font-medium">Leaving date{field('leaving_date', 'date')}</label></div><p className="mt-3 text-xs text-slate-500">Leaving date ke 15 din baad listing expire hogi. Leaving date na ho to listing 90 din ke liye active rahegi.</p></section>
            <section><h2 className="font-bold">Location</h2><p className="mt-2 text-sm text-slate-500">Google Maps se area select karein. Public ko exact flat/house address nahi dikhaya jayega.</p><label className="mt-4 block text-sm font-medium">Search area or landmark<input ref={addressRef} value={form.data.approximate_address} onChange={(e) => form.setData('approximate_address', e.target.value)} placeholder="Search with Google Maps" className="field mt-2" /></label>{!googleMapsKey && <p className="mt-2 text-xs text-amber-700">Maps key configure nahi hai; aap manual location fields use kar sakte hain.</p>}<div className="mt-4 grid gap-4 sm:grid-cols-2"><label className="text-sm font-medium">City{field('city')}</label><label className="text-sm font-medium">Area{field('area')}</label><label className="text-sm font-medium">Locality{field('locality')}</label><label className="text-sm font-medium">Pincode{field('pincode')}</label></div>{mapsReady && <div ref={mapRef} className="mt-4 h-48 rounded-2xl bg-slate-100" />}<p className="mt-2 text-xs text-slate-500">Selected coordinates secure database me save honge; public map approximate 400m radius show karega.</p></section>
            <section><h2 className="font-bold">Room photos</h2><p className="mt-2 text-sm text-slate-500">Maximum 6 photos, JPG/PNG/WEBP, each up to 5 MB. First photo cover rahegi.</p><label className="mt-4 block cursor-pointer rounded-2xl border border-dashed border-blue-300 bg-blue-50 p-5 text-center text-sm font-semibold text-blue-700"><input type="file" accept="image/jpeg,image/png,image/webp" multiple className="hidden" onChange={(e) => form.setData('images', Array.from(e.target.files ?? []).slice(0, 6))} />{form.data.images.length ? `${form.data.images.length} photo(s) selected` : 'Select room photos'}</label></section><section><h2 className="font-bold">Owner contact</h2><p className="mt-2 text-sm text-slate-500">Ye number public listing me nahi dikhega. Tenant ka number kabhi store nahi hoga.</p><div className="mt-4 grid gap-4 sm:grid-cols-2"><label className="text-sm font-medium">Owner name{field('owner_name')}</label><label className="text-sm font-medium">Owner phone{field('owner_phone', 'tel')}</label></div></section>
            {(Object.keys(form.errors).length > 0) && <div className="rounded-2xl bg-red-50 p-4 text-sm text-red-700">Form me kuch fields check karein: {Object.values(form.errors).join(' ')}</div>}
            <button disabled={form.processing} className="button">{form.processing ? 'Publishing…' : 'Publish room listing'}</button>
        </form></div></main></>
    );
}
