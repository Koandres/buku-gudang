import { Head } from '@inertiajs/react';
import Layout from '../Components/Layout';
import MonthChart from '../Components/MonthChart';

function Stat({ label, value }) {
    return (
        <div className="panel panel-pad">
            <div className="small muted">{label}</div>
            <div style={{ fontFamily: 'var(--font-number)', fontSize: '28px' }}>{value}</div>
        </div>
    );
}

export default function Dashboard({ stats, chart }) {
    return (
        <Layout title="Dashboard" subtitle="Ringkasan kondisi pengadaan dan gudang">
            <Head title="Dashboard" />
            <div className="stack">
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
                    <Stat label="PR menunggu persetujuan" value={stats.menunggu} />
                    <Stat label="PO masih terbuka" value={stats.poTerbuka} />
                    <Stat label="Barang di bawah stok minimum" value={stats.stokMinimum} />
                </div>
                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '12px' }}>Nilai pembelian enam bulan terakhir</div>
                    <MonthChart data={chart} />
                </div>
            </div>
        </Layout>
    );
}
