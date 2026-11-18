import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import Pagination from '../../Components/Pagination';
import Num from '../../Components/Num';

export default function ReceiptsIndex({ receipts, q, warehouseId, warehouses }) {
    const user = usePage().props.auth.user;
    const bisaTerima = ['gudang', 'admin'].includes(user?.role);
    const columns = [
        { key: 'nomor', label: 'Nomor', render: (row) => <Link href={`/goods-receipts/${row.id}`}>{row.nomor}</Link> },
        { key: 'tanggal', label: 'Tanggal' },
        { key: 'po', label: 'Dari PO', render: (row) => row.order?.nomor ?? '—' },
        { key: 'gudang', label: 'Gudang', render: (row) => row.warehouse?.nama ?? '—' },
        { key: 'baris', label: 'Baris', align: 'num', render: (row) => <Num value={row.items.length} /> },
        {
            key: 'jumlah', label: 'Total diterima', align: 'num',
            render: (row) => <Num value={row.items.reduce((sum, item) => sum + item.jumlah, 0)} />,
        },
        { key: 'user', label: 'Penerima', render: (row) => row.user?.name },
        { key: 'aksi', label: 'Aksi', render: (row) => <Link href={`/goods-receipts/${row.id}`}>Lihat</Link> },
    ];

    return (
        <Layout title="Penerimaan Barang" subtitle="Catat barang masuk dari pesanan pembelian">
            <Head title="Penerimaan Barang" />
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="q">Cari</label>
                    <input id="q" name="q" className="input" defaultValue={q} placeholder="Nomor GR, PO, atau gudang" />
                </div>
                <div className="field">
                    <label htmlFor="warehouse_id">Gudang</label>
                    <select id="warehouse_id" name="warehouse_id" className="select" defaultValue={warehouseId ?? ''}>
                        <option value="">Semua</option>
                        {warehouses.map((warehouse) => (
                            <option key={warehouse.id} value={warehouse.id}>{warehouse.nama}</option>
                        ))}
                    </select>
                </div>
                <button className="btn" type="submit">Filter</button>
                {bisaTerima && <Link className="btn btn-primary" href="/goods-receipts/create">Terima Barang</Link>}
            </form>
            <div className="panel">
                <DataTable columns={columns} rows={receipts.data} empty="Belum ada penerimaan." />
                <Pagination meta={receipts} />
            </div>
        </Layout>
    );
}
