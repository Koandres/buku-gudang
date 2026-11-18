import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import StatusBadge from '../../Components/StatusBadge';
import Num, { Rupiah } from '../../Components/Num';
import { requestStatus, orderStatus } from '../../Components/statusMeta';

export default function RequestShow({ request, timeline, can }) {
    const meta = requestStatus[request.status] ?? { label: request.status, tone: 'netral' };
    const reject = useForm({ alasan: '' });

    const rejectSubmit = (event) => {
        event.preventDefault();
        reject.post(`/purchase-requests/${request.id}/reject`, { preserveScroll: true, onSuccess: () => reject.reset() });
    };

    const columns = [
        { key: 'kode', label: 'Kode', render: (row) => row.item?.kode },
        { key: 'nama', label: 'Barang', render: (row) => row.item?.nama },
        { key: 'satuan', label: 'Satuan', render: (row) => row.item?.satuan },
        { key: 'jumlah', label: 'Jumlah', align: 'num', render: (row) => <Num value={row.jumlah} /> },
        { key: 'dipesan', label: 'Sudah dipesan', align: 'num', render: (row) => <Num value={row.jumlah_dipesan} /> },
    ];

    return (
        <Layout title={`Permintaan ${request.nomor}`} subtitle=" Detail permintaan pembelian">
            <Head title={request.nomor} />
            <div className="stack">
                <div className="panel panel-pad">
                    <div className="doc-head">
                        <dl className="doc-meta"><dt>Nomor</dt><dd>{request.nomor}</dd></dl>
                        <dl className="doc-meta"><dt>Tanggal</dt><dd>{request.tanggal}</dd></dl>
                        <dl className="doc-meta"><dt>Status</dt><dd><StatusBadge status={request.status} label={meta.label} tone={meta.tone} /></dd></dl>
                        <dl className="doc-meta"><dt>Pemohon</dt><dd>{request.user?.name}</dd></dl>
                    </div>
                    <p style={{ marginTop: '12px' }}>{request.keperluan}</p>
                    {request.alasan_ditolak && <p className="error">Alasan penolakan: {request.alasan_ditolak}</p>}
                </div>

                <div className="panel">
                    <DataTable columns={columns} rows={request.items} />
                </div>

                <div className="panel panel-pad">
                    <div className="page-sub" style={{ marginBottom: '8px' }}>Pesanan dari PR ini</div>
                    {request.purchase_orders?.length ? (
                        <ul>
                            {request.purchase_orders.map((order) => (
                                <li key={order.id}>
                                    <Link href={`/purchase-orders/${order.id}`}>{order.nomor}</Link>{' '}
                                    <StatusBadge status={order.status} label={orderStatus[order.status]?.label ?? order.status} tone={orderStatus[order.status]?.tone} />
                                </li>
                            ))}
                        </ul>
                    ) : <span className="muted small">Belum ada PO.</span>}
                </div>

                <div className="panel panel-pad no-print">
                    {can.edit && (
                        <div className="row">
                            <Link className="btn" href={`/purchase-requests/${request.id}/edit`}>Ubah draft</Link>
                            <Link className="btn" href={`/purchase-requests/${request.id}`} method="delete" as="button">Hapus draft</Link>
                            <Link className="btn btn-primary" href={`/purchase-requests/${request.id}/submit`} method="post" as="button">Ajukan PR</Link>
                        </div>
                    )}
                    {can.approve && (
                        <div className="stack">
                            <Link className="btn btn-primary" href={`/purchase-requests/${request.id}/approve`} method="post" as="button">Setujui PR</Link>
                            <form onSubmit={rejectSubmit} className="form-grid">
                                <div className="field">
                                    <label htmlFor="alasan">Alasan penolakan</label>
                                    <input id="alasan" className={`input ${reject.errors.alasan ? 'invalid' : ''}`} value={reject.data.alasan} onChange={(e) => reject.setData('alasan', e.target.value)} />
                                    {reject.errors.alasan && <span className="error">{reject.errors.alasan}</span>}
                                </div>
                                <button className="btn" type="submit" disabled={reject.processing}>Tolak PR</button>
                            </form>
                        </div>
                    )}
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
