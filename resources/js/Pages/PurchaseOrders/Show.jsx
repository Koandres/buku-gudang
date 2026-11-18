import { Head, Link } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import StatusBadge from '../../Components/StatusBadge';
import Num, { Rupiah } from '../../Components/Num';
import { orderStatus } from '../../Components/statusMeta';

export default function OrderShow({ order, total, timeline, can }) {
    const meta = orderStatus[order.status] ?? { label: order.status, tone: 'netral' };

    const columns = [
        { key: 'kode', label: 'Kode', render: (row) => row.item?.kode },
        { key: 'nama', label: 'Barang', render: (row) => row.nama ?? row.item?.nama },
        { key: 'satuan', label: 'Satuan', render: (row) => row.item?.satuan },
        { key: 'jumlah', label: 'Dipesan', align: 'num', render: (row) => <Num value={row.jumlah} /> },
        { key: 'diterima', label: 'Diterima', align: 'num', render: (row) => <Num value={row.jumlah_diterima} /> },
        { key: 'sisa', label: 'Sisa', align: 'num', render: (row) => <Num value={row.jumlah - row.jumlah_diterima} /> },
        { key: 'harga', label: 'Harga', align: 'num', render: (row) => <Rupiah value={row.harga} /> },
        { key: 'subtotal', label: 'Subtotal', align: 'num', render: (row) => <Rupiah value={row.harga * row.jumlah} /> },
    ];

    return (
        <Layout title={`Pesanan ${order.nomor}`} subtitle=" Detail pesanan pembelian">
            <Head title={order.nomor} />
            <div className="stack">
                <div className="panel panel-pad">
                    <div className="doc-head">
                        <dl className="doc-meta"><dt>Nomor</dt><dd>{order.nomor}</dd></dl>
                        <dl className="doc-meta"><dt>Tanggal</dt><dd>{order.tanggal}</dd></dl>
                        <dl className="doc-meta">
                            <dt>Status</dt>
                            <dd><StatusBadge status={order.status} label={meta.label} tone={meta.tone} /></dd>
                        </dl>
                        <dl className="doc-meta"><dt>Supplier</dt><dd>{order.supplier?.nama ?? '—'}</dd></dl>
                        <dl className="doc-meta">
                            <dt>Dari PR</dt>
                            <dd>{order.request ? <Link href={`/purchase-requests/${order.purchase_request_id}`}>{order.request.nomor}</Link> : '—'}</dd>
                        </dl>
                        <dl className="doc-meta"><dt>Dibuat oleh</dt><dd>{order.user?.name ?? '—'}</dd></dl>
                    </div>
                    {order.catatan && <p style={{ marginTop: '12px' }}>{order.catatan}</p>}
                </div>

                <div className="panel">
                    <DataTable columns={columns} rows={order.items} empty="Belum ada barang." />
                    <div className="panel-pad row" style={{ justifyContent: 'flex-end' }}>
                        <strong>Total: <Rupiah value={total} /></strong>
                    </div>
                </div>

                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '8px' }}>Penerimaan dari PO ini</div>
                    {order.receipts?.length ? (
                        <ul>
                            {order.receipts.map((receipt) => (
                                <li key={receipt.id}>
                                    <Link href={`/goods-receipts/${receipt.id}`}>{receipt.nomor}</Link>{' '}
                                    <span className="small muted">{receipt.status}</span>
                                </li>
                            ))}
                        </ul>
                    ) : <span className="muted small">Belum ada penerimaan.</span>}
                </div>

                <div className="panel panel-pad no-print">
                    <div className="row">
                        <button className="btn" type="button" onClick={() => window.print()}>Cetak PO</button>
                        {can.issue && order.status === 'draft' && (
                            <Link className="btn btn-primary" href={`/purchase-orders/${order.id}/issue`} method="post" as="button">Terbitkan PO</Link>
                        )}
                        {can.cancel && ['draft', 'terbit'].includes(order.status) && (
                            <Link className="btn btn-danger" href={`/purchase-orders/${order.id}/cancel`} method="post" as="button">Batalkan PO</Link>
                        )}
                        <Link className="btn" href="/purchase-orders">Kembali</Link>
                    </div>
                </div>

                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '8px' }}>Riwayat status</div>
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
