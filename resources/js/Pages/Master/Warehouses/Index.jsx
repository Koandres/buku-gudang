import { Head, Link } from '@inertiajs/react';
import MasterIndex from '../../../Components/MasterIndex';

const columns = (canManage) => [
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Nama' },
    {
        key: 'aksi', label: 'Aksi',
        render: (row) => canManage && <Link href={`/warehouses/${row.id}/edit`}>Ubah</Link>,
    },
];

export default function WarehousesIndex({ warehouses, q, canManage }) {
    return (
        <>
            <Head title="Gudang" />
            <MasterIndex
                title="Gudang"
                subtitle="Daftar lokasi penyimpanan"
                href="/warehouses"
                columns={columns(canManage)}
                rows={warehouses.data}
                meta={warehouses}
                q={q}
                canManage={canManage}
                createLabel="Tambah gudang"
            />
        </>
    );
}
