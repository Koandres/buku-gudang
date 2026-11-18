import ReportShell from '../../Components/ReportShell';
import Num from '../../Components/Num';

const columns = [
    { key: 'gudang', label: 'Gudang' },
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Barang' },
    { key: 'satuan', label: 'Satuan' },
    { key: 'saldo', label: 'Saldo', align: 'num', render: (row) => <Num value={row.saldo} /> },
];

export default function ReportStock({ rows }) {
    return (
        <ReportShell
            title="Stok per Gudang"
            subtitle="Saldo akhir setiap barang pada setiap gudang"
            jenis="stock"
            columns={columns}
            rows={rows}
            empty="Belum ada stok."
        />
    );
}
