import ReportShell from '../../Components/ReportShell';
import { Rupiah } from '../../Components/Num';

const columns = [
    { key: 'nomor', label: 'Nomor PO' },
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'sisa', label: 'Sisa jumlah', align: 'num' },
    { key: 'nilai_sisa', label: 'Nilai sisa', align: 'num', render: (row) => <Rupiah value={row.nilai_sisa} /> },
    { key: 'status', label: 'Status' },
];

export default function ReportOutstanding({ rows }) {
    return (
        <ReportShell
            title="PO Outstanding"
            subtitle="Pesanan yang belum selesai atau dibatalkan"
            jenis="outstanding"
            columns={columns}
            rows={rows}
            empty="Tidak ada PO terbuka."
        />
    );
}
