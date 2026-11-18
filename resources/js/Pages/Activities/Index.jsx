import { Head } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import Pagination from '../../Components/Pagination';

export default function ActivitiesIndex({ logs, q }) {
    const columns = [
        { key: 'waktu', label: 'Waktu', render: (row) => <span className="small">{row.waktu}</span> },
        { key: 'aksi', label: 'Aksi' },
        { key: 'objek', label: 'Objek' },
        { key: 'ringkasan', label: 'Ringkasan', render: (row) => row.ringkasan ?? '—' },
        { key: 'user', label: 'Oleh', render: (row) => row.user?.name ?? 'Sistem' },
    ];

    return (
        <Layout title="Log Aktivitas" subtitle="Riwayat perubahan pada dokumen dan data master">
            <Head title="Log Aktivitas" />
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="q">Cari</label>
                    <input id="q" name="q" className="input" defaultValue={q} placeholder="Aksi, objek, atau ringkasan" />
                </div>
                <button className="btn" type="submit">Cari</button>
            </form>
            <div className="panel">
                <DataTable columns={columns} rows={logs.data} empty="Belum ada aktivitas." />
                <Pagination meta={logs} />
            </div>
        </Layout>
    );
}
