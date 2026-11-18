import { Head, Link } from '@inertiajs/react';
import MasterIndex from '../../../Components/MasterIndex';
import StatusBadge from '../../../Components/StatusBadge';
import Num, { Rupiah } from '../../../Components/Num';

const columns = (canManage) => [
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Nama' },
    { key: 'satuan', label: 'Satuan' },
    { key: 'harga_acuan', label: 'Harga acuan', align: 'num', render: (row) => <Rupiah value={row.harga_acuan} /> },
    { key: 'stok_minimum', label: 'Stok minimum', align: 'num', render: (row) => <Num value={row.stok_minimum} /> },
    {
        key: 'aktif', label: 'Status',
        render: (row) => <StatusBadge status={row.aktif ? 'Aktif' : 'Nonaktif'} label={row.aktif ? 'Aktif' : 'Nonaktif'} tone={row.aktif ? 'aksen' : 'netral'} />,
    },
    {
        key: 'aksi', label: 'Aksi',
        render: (row) => canManage && (
            <span className="row">
                <Link href={`/items/${row.id}/edit`}>Ubah</Link>
                <Link href={`/items/${row.id}`} method="delete" as="button" className="btn">{row.aktif ? 'Nonaktifkan' : 'Aktifkan'}</Link>
            </span>
        ),
    },
];

export default function ItemsIndex({ items, q, canManage }) {
    return (
        <>
            <Head title="Barang" />
            <MasterIndex
                title="Barang"
                subtitle="Daftar barang dan batas stok minimum"
                href="/items"
                columns={columns(canManage)}
                rows={items.data}
                meta={items}
                q={q}
                canManage={canManage}
                createLabel="Tambah barang"
            />
        </>
    );
}
