import { Head, Link } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import Num from '../../Components/Num';

export default function ReceiptShow({ receipt, timeline }) {
    const columns = [
        { key: 'kode', label: 'Kode', render: (row) => row.orderItem?.item?.kode },
        { key: 'nama', label: 'Barang', render: (row) => row.orderItem?.item?.nama },
        { key: 'satuan', label: 'Satuan', render: (row) => row.orderItem?.item?.satuan },
        {
            key: 'dipesan', label: 'Dipesan', align: 'num',
            render: (row) => <Num value={row.orderItem?.jumlah} />,
        },
        {
            key: 'diterimaTotal', label: 'Total diterima', align: 'num',
            render: (row) => <Num value={row.orderItem?.jumlah_diterima} />,
        },
        { key: 'jumlah', label: 'Diterima kali ini', align: 'num', render: (row) => <Num value={row.jumlah} /> },
    ];

    return (
        <Layout title={`Penerimaan ${receipt.nomor}`} subtitle=" Detail penerimaan barang">
            <Head title={receipt.nomor} />
            <div className="stack">
                <div className="panel panel-pad">
                    <div className="doc-head">
                        <dl className="doc-meta"><dt>Nomor</dt><dd>{receipt.nomor}</dd></dl>
                        <dl className="doc-meta"><dt>Tanggal</dt><dd>{receipt.tanggal}</dd></dl>
                        <dl className="doc-meta">
                            <dt>Dari PO</dt>
                            <dd>{receipt.order ? <Link href={`/purchase-orders/${receipt.purchase_order_id}`}>{receipt.order.nomor}</Link> : '—'}</dd>
                        </dl>
                        <dl className="doc-meta"><dt>Gudang</dt><dd>{receipt.warehouse?.nama ?? '—'}</dd></dl>
                        <dl className="doc-meta"><dt>Supplier</dt><dd>{receipt.order?.supplier?.nama ?? '—'}</dd></dl>
                        <dl className="doc-meta"><dt>Penerima</dt><dd>{receipt.user?.name ?? '—'}</dd></dl>
                    </div>
                    {receipt.catatan && <p style={{ marginTop: '12px' }}>{receipt.catatan}</p>}
                </div>

                <div className="panel">
                    <DataTable columns={columns} rows={receipt.items} empty="Belum ada barang." />
                </div>

                <div className="panel panel-pad no-print row">
                    <Link className="btn" href="/stock">Lihat Kartu Stok</Link>
                    <Link className="btn" href="/goods-receipts">Kembali</Link>
                </div>

                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '8px' }}>Riwayat</div>
                    <ul className="timeline">
                        {timeline.map((entry, index) => (
                            <li key={index}>
                                <span className="small muted">{entry.waktu}</span>
                                <span>{entry.aksi}</span>
                                <span className="small muted">{entry.ringkasan}</span>
                            </li>
                        ))}
                        {timeline.length === 0 && <li className="muted">Belum ada riwayat.</li>}
                    </ul>
                </div>
            </div>
        </Layout>
    );
}
