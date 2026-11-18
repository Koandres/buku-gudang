import { Head, Link } from '@inertiajs/react';
import MasterIndex from '../../../Components/MasterIndex';
import StatusBadge from '../../../Components/StatusBadge';

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Peran', render: (row) => row.role.charAt(0).toUpperCase() + row.role.slice(1) },
    {
        key: 'active', label: 'Status',
        render: (row) => <StatusBadge status={row.active ? 'Aktif' : 'Nonaktif'} label={row.active ? 'Aktif' : 'Nonaktif'} tone={row.active ? 'aksen' : 'netral'} />,
    },
    {
        key: 'aksi', label: 'Aksi',
        render: (row) => (
            <span className="row">
                <Link href={`/users/${row.id}/edit`}>Ubah</Link>
                <Link href={`/users/${row.id}`} method="delete" as="button" className="btn">{row.active ? 'Nonaktifkan' : 'Aktifkan'}</Link>
            </span>
        ),
    },
];

export default function UsersIndex({ users, q }) {
    return (
        <>
            <Head title="Pengguna" />
            <MasterIndex
                title="Pengguna"
                subtitle="Akun dan peran pengguna"
                href="/users"
                columns={columns}
                rows={users.data}
                meta={users}
                q={q}
                canManage={true}
                createLabel="Tambah pengguna"
            />
        </>
    );
}
