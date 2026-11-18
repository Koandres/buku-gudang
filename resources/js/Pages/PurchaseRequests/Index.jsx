import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import { requestStatus } from '../../Components/statusMeta';
import Num from '../../Components/Num';

export default function RequestsIndex({ requests, q, status, statuses }) {
    const user = usePage().props.auth.user;
    const columns = [
        { key: 'nomor', label: 'Nomor', render: (row) => <Link href={`/purchase-requests/${row.id}`}>{row.nomor}</Link> },
        { key: 'tanggal', label: 'Tanggal' },
        { key: 'pemohon', label: 'Pemohon', render: (row) => row.user?.name },
        { key: 'keperluan', label: 'Keperluan' },
        { key: 'jumlah', label: 'Baris', align: 'num', render: (row) => <Num value={row.items.length} /> },
        {
            key: 'status', label: 'Status',
            render: (row) => <StatusBadge status={row.status} label={requestStatus[row.status]?.label ?? row.status} tone={requestStatus[row.status]?.tone} />,
        },
        {
            key: 'aksi', label: 'Aksi',
            render: (row) => {
                const own = row.user_id === user?.id || user?.role === 'admin';
                const draft = row.status === 'draft';
                return (
                    <span className="row">
                        <Link href={`/purchase-requests/${row.id}`}>Lihat</Link>
                        {draft && own && <Link href={`/purchase-requests/${row.id}/edit`}>Ubah</Link>}
                        {draft && own && <Link href={`/purchase-requests/${row.id}/submit`} method="post" as="button" className="btn">Ajukan</Link>}
                    </span>
                );
            },
        },
    ];

    return (
        <Layout title="Permintaan Pembelian" subtitle="Daftar permintaan barang dari pemohon">
            <Head title="Permintaan Pembelian" />
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="q">Cari</label>
                    <input id="q" name="q" className="input" defaultValue={q} placeholder="Nomor atau keperluan" />
                </div>
                <div className="field">
                    <label htmlFor="status">Status</label>
                    <select id="status" name="status" className="select" defaultValue={status}>
                        <option value="">Semua</option>
                        {statuses.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                    </select>
                </div>
                <button className="btn" type="submit">Filter</button>
                {['pemohon', 'admin'].includes(user?.role) && (
                    <Link className="btn btn-primary" href="/purchase-requests/create">Buat PR</Link>
                )}
            </form>
            <div className="panel">
                <DataTable columns={columns} rows={requests.data} empty="Belum ada permintaan." />
                <Pagination meta={requests} />
            </div>
        </Layout>
    );
}
