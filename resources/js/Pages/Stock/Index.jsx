import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import DataTable from '../../Components/DataTable';
import StatusBadge from '../../Components/StatusBadge';
import Num from '../../Components/Num';
import { movementType } from '../../Components/statusMeta';

export default function StockIndex({ rows, saldoAkhir, itemId, warehouseId, items, warehouses }) {
    const user = usePage().props.auth.user;
    const bisaSesuaikan = ['gudang', 'admin'].includes(user?.role);
    const columns = [
        { key: 'waktu', label: 'Waktu', render: (row) => <span className="small">{row.waktu}</span> },
        { key: 'item', label: 'Barang', render: (row) => `${row.item?.kode ?? ''} — ${row.item?.nama ?? ''}` },
        { key: 'warehouse', label: 'Gudang', render: (row) => row.warehouse?.nama },
        {
            key: 'jenis', label: 'Jenis',
            render: (row) => (
                <StatusBadge
                    status={row.jenis}
                    label={movementType[row.jenis]?.label ?? row.jenis}
                    tone={movementType[row.jenis]?.tone}
                />
            ),
        },
        {
            key: 'quantity', label: 'Mutasi', align: 'num',
            render: (row) => <Num value={row.jenis === 'keluar' ? -row.quantity : row.quantity} />,
        },
        { key: 'saldo', label: 'Saldo', align: 'num', render: (row) => <Num value={row.saldo} /> },
        { key: 'reference', label: 'Referensi', render: (row) => <span className="small">{row.reference}</span> },
        { key: 'catatan', label: 'Catatan', render: (row) => row.catatan ?? '—' },
        { key: 'user', label: 'Oleh', render: (row) => row.user?.name },
    ];

    return (
        <Layout title="Kartu Stok" subtitle="Buku besar pergerakan stok per barang dan gudang">
            <Head title="Kartu Stok" />
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="item_id">Barang</label>
                    <select id="item_id" name="item_id" className="select" defaultValue={itemId ?? ''}>
                        <option value="">Semua</option>
                        {items.map((item) => (
                            <option key={item.id} value={item.id}>{item.kode} — {item.nama}</option>
                        ))}
                    </select>
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
                {bisaSesuaikan && <Link className="btn btn-primary" href="/stock/adjust">Penyesuaian Stok</Link>}
            </form>
            <div className="panel">
                <div className="panel-pad row" style={{ justifyContent: 'space-between' }}>
                    <span className="muted">Saldo akhir filter: <Num value={saldoAkhir} /></span>
                    <span className="small muted">Saldo dihitung dari seluruh mutasi.</span>
                </div>
                <DataTable columns={columns} rows={rows} empty="Belum ada pergerakan stok." />
            </div>
        </Layout>
    );
}
