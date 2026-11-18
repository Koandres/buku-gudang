import ReportShell from '../../Components/ReportShell';
import Num, { Rupiah } from '../../Components/Num';

const columns = [
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Barang' },
    { key: 'satuan', label: 'Satuan' },
    { key: 'saldo', label: 'Saldo', align: 'num', render: (row) => <Num value={row.saldo} /> },
    { key: 'minimum', label: 'Minimum', align: 'num', render: (row) => <Num value={row.minimum} /> },
    { key: 'kurang', label: 'Kurang', align: 'num', render: (row) => <Num value={row.kurang} /> },
    { key: 'estimasi', label: 'Estimasi', align: 'num', render: (row) => <Rupiah value={row.estimasi} /> },
];

export default function ReportReorder({ rows }) {
    return (
        <ReportShell
            title="Perlu Dipesan"
            subtitle="Barang aktif dengan saldo di bawah stok minimum"
            jenis="reorder"
            columns={columns}
            rows={rows}
            empty="Semua barang berada di atas stok minimum."
        />
    );
}
