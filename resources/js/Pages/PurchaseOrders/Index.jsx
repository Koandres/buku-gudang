import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import { orderStatus } from '../../Components/statusMeta';
import { Rupiah } from '../../Components/Num';

export default function OrdersIndex({ orders, q, status, supplierId, suppliers, statuses }) {
    const user = usePage().props.auth.user;
    const bisaKelola = ['pembelian', 'admin'].includes(user?.role);
    const columns = [
        { key: 'nomor', label: 'Nomor', render: (row) => <Link href={`/purchase-orders/${row.id}`}>{row.nomor}</Link> },
        { key: 'tanggal', label: 'Tanggal' },
        { key: 'supplier', label: 'Supplier', render: (row) => row.supplier?.nama },
        { key: 'pr', label: 'Dari PR', render: (row) => row.request?.nomor ?? '—' },
        {
            key: 'total', label: 'Total', align: 'num',
            render: (row) => <Rupiah value={row.items.reduce((sum, item) => sum + item.harga * item.jumlah, 0)} />,
        },
        {
            key: 'status', label: 'Status',
            render: (row) => (
                <StatusBadge
                    status={row.status}
                    label={orderStatus[row.status]?.label ?? row.status}
                    tone={orderStatus[row.status]?.tone}
                />
            ),
        },
        {
            key: 'aksi', label: 'Aksi',
            render: (row) => (
                <span className="row">
                    <Link href={`/purchase-orders/${row.id}`}>Lihat</Link>
                    {bisaKelola && row.status === 'draft' && (
                        <Link href={`/purchase-orders/${row.id}/issue`} method="post" as="button" className="btn">Terbitkan</Link>
                    )}
                    {bisaKelola && ['draft', 'terbit'].includes(row.status) && (
                        <Link href={`/purchase-orders/${row.id}/cancel`} method="post" as="button" className="btn">Batalkan</Link>
                    )}
                </span>
            ),
        },
    ];

    return (
        <Layout title="Pesanan Pembelian" subtitle="Pesanan kepada supplier berdasarkan PR yang disetujui">
            <Head title="Pesanan Pembelian" />
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="q">Cari</label>
                    <input id="q" name="q" className="input" defaultValue={q} placeholder="Nomor, catatan, atau supplier" />
                </div>
                <div className="field">
                    <label htmlFor="status">Status</label>
                    <select id="status" name="status" className="select" defaultValue={status}>
                        <option value="">Semua</option>
                        {statuses.map((item) => <option key={item.value} value={item.value}>{item.label}</option>)}
                    </select>
                </div>
                <div className="field">
                    <label htmlFor="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" className="select" defaultValue={supplierId ?? ''}>
                        <option value="">Semua</option>
                        {suppliers.map((supplier) => (
                            <option key={supplier.id} value={supplier.id}>{supplier.nama}</option>
                        ))}
                    </select>
                </div>
                <button className="btn" type="submit">Filter</button>
                {bisaKelola && <Link className="btn btn-primary" href="/purchase-orders/create">Buat PO dari PR</Link>}
            </form>
            <div className="panel">
                <DataTable columns={columns} rows={orders.data} empty="Belum ada pesanan." />
                <Pagination meta={orders} />
            </div>
        </Layout>
    );
}
