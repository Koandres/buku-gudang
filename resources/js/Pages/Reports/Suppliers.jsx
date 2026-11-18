import ReportShell from '../../Components/ReportShell';
import Num, { Rupiah } from '../../Components/Num';

const columns = [
    { key: 'kode', label: 'Kode' },
    { key: 'nama', label: 'Supplier' },
    { key: 'jumlah_po', label: 'Jumlah PO', align: 'num', render: (row) => <Num value={row.jumlah_po} /> },
    { key: 'total_nilai', label: 'Total nilai', align: 'num', render: (row) => <Rupiah value={row.total_nilai} /> },
    { key: 'total_diterima', label: 'Total diterima', align: 'num', render: (row) => <Rupiah value={row.total_diterima} /> },
];

export default function ReportSuppliers({ rows }) {
    return (
        <ReportShell
            title="Pembelian per Supplier"
            subtitle="Akumulasi nilai pesanan di luar PO dibatalkan"
            jenis="suppliers"
            columns={columns}
            rows={rows}
            empty="Belum ada pesanan per supplier."
        />
    );
}
