import { Link } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import MasterIndex from '../../../Components/MasterIndex';
import StatusBadge from '../../../Components/StatusBadge';

const columns = (canManage) => [
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Nama' },
    { key: 'kontak', label: 'Kontak' },
    { key: 'telepon', label: 'Telepon' },
    {
        key: 'aktif', label: 'Status',
        render: (row) => <StatusBadge status={row.aktif ? 'Aktif' : 'Nonaktif'} label={row.aktif ? 'Aktif' : 'Nonaktif'} tone={row.aktif ? 'aksen' : 'netral'} />,
    },
    {
        key: 'aksi', label: 'Aksi',
        render: (row) => (
            <span className="row">
                {canManage && <Link href={`/suppliers/${row.id}/edit`}>Ubah</Link>}
                {canManage && <Link href={`/suppliers/${row.id}`} method="delete" as="button" className="btn">{row.aktif ? 'Nonaktifkan' : 'Aktifkan'}</Link>}
            </span>
        ),
    },
];

export default function SuppliersIndex({ suppliers, q, canManage }) {
    return (
        <>
            <Head title="Supplier" />
            <MasterIndex
                title="Supplier"
                subtitle="Daftar pemasok barang"
                href="/suppliers"
                columns={columns(canManage)}
                rows={suppliers.data}
                meta={suppliers}
                q={q}
                canManage={canManage}
                createLabel="Tambah supplier"
            />
        </>
    );
}
