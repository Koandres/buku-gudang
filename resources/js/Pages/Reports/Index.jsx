import { Head } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import MonthChart from '../../Components/MonthChart';
import { Rupiah } from '../../Components/Num';

export default function ReportsIndex({ ringkasan, chart }) {
    const angka = Object.fromEntries(ringkasan.angka.map((item) => [item.label, item.nilai]));
    const kunciNilai = Object.keys(angka)[0];
    const kartu = [
        ['PR menunggu persetujuan', ringkasan.pr.find((item) => item.label === 'Diajukan')?.jumlah ?? 0],
        ['PO masih terbuka', ['Draft', 'Terbit', 'Sebagian diterima'].reduce(
            (total, label) => total + (ringkasan.po.find((item) => item.label === label)?.jumlah ?? 0), 0,
        )],
        [kunciNilai, angka[kunciNilai]],
    ];

    return (
        <Layout
            title="Ringkasan Laporan"
            subtitle="Ikhtisar kondisi pengadaan dan stok"
            actions={<a className="btn" href="/reports/export?jenis=ringkasan">Ekspor CSV</a>}
        >
            <Head title="Ringkasan Laporan" />
            <div className="stack">
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
                    {kartu.map(([label, nilai]) => (
                        <div className="panel panel-pad" key={label}>
                            <div className="small muted">{label}</div>
                            <div style={{ fontFamily: 'var(--font-number)', fontSize: '24px' }}>
                                {label === kunciNilai ? <Rupiah value={nilai} /> : nilai}
                            </div>
                        </div>
                    ))}
                </div>
                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '12px' }}>Nilai pembelian enam bulan terakhir</div>
                    <MonthChart data={chart} />
                </div>
                <div className="panel">
                    <table className="ledger">
                        <thead>
                            <tr><th>Kelompok</th><th>Status / Keterangan</th><th className="num">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            {ringkasan.pr.map((item) => (
                                <tr key={`pr-${item.label}`}>
                                    <td>PR</td><td>{item.label}</td><td className="num">{item.jumlah}</td>
                                </tr>
                            ))}
                            {ringkasan.po.map((item) => (
                                <tr key={`po-${item.label}`}>
                                    <td>PO</td><td>{item.label}</td><td className="num">{item.jumlah}</td>
                                </tr>
                            ))}
                            {ringkasan.angka.map((item) => (
                                <tr key={`umum-${item.label}`}>
                                    <td>Umum</td><td>{item.label}</td><td className="num">{item.nilai.toLocaleString('id-ID')}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </Layout>
    );
}
