import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import FormField from '../../Components/FormField';
import ItemRows from '../../Components/ItemRows';
import Num from '../../Components/Num';

function barisDariPO(order) {
    return (order?.items ?? [])
        .filter((line) => Number(line.sisa) > 0)
        .map((line) => ({
            purchase_order_item_id: String(line.id),
            kode: line.kode ?? '',
            nama: line.nama,
            satuan: line.satuan ?? '',
            sisa: String(line.sisa),
            jumlah: String(line.sisa),
        }));
}

export default function ReceiptForm({ orders, selectedOrder, warehouses, tanggal }) {
    const awal = orders.find((order) => String(order.id) === String(selectedOrder)) ?? orders[0] ?? null;
    const { data, setData, post, processing, errors } = useForm({
        tanggal,
        purchase_order_id: awal ? String(awal.id) : '',
        warehouse_id: warehouses[0] ? String(warehouses[0].id) : '',
        catatan: '',
        items: barisDariPO(awal),
    });

    const pilihPO = (id) => {
        const order = orders.find((item) => String(item.id) === String(id));
        setData({
            ...data,
            purchase_order_id: id ? String(id) : '',
            items: barisDariPO(order),
        });
    };

    const ubahBaris = (index, key, value) => {
        setData('items', data.items.map((row, i) => (i === index ? { ...row, [key]: value } : row)));
    };

    const hapusBaris = (index) => {
        setData('items', data.items.filter((_, i) => i !== index));
    };

    const submit = (event) => {
        event.preventDefault();
        post('/goods-receipts');
    };

    const columns = [
        { key: 'purchase_order_item_id', label: '', type: 'hidden' },
        { key: 'nama', label: 'Barang', readOnly: true, displayKey: 'nama' },
        { key: 'kode', label: 'Kode', readOnly: true },
        { key: 'satuan', label: 'Satuan', readOnly: true },
        { key: 'sisa', label: 'Sisa PO', align: 'num', readOnly: true },
        { key: 'jumlah', label: 'Jumlah diterima', type: 'number', align: 'num' },
    ];

    const total = data.items.reduce((sum, row) => sum + (Number(row.jumlah) || 0), 0);
    const poTerpilih = orders.find((order) => String(order.id) === String(data.purchase_order_id));

    return (
        <Layout title="Terima Barang" subtitle="Catat penerimaan sebagian atau seluruh barang dari PO">
            <Head title="Terima Barang" />
            {orders.length === 0 && (
                <div className="flash error">Belum ada PO terbit dengan sisa barang yang bisa diterima.</div>
            )}
            <form onSubmit={submit} className="stack">
                <div className="panel panel-pad">
                    <div className="form-grid">
                        <FormField label="Dari PO" name="purchase_order_id" error={errors.purchase_order_id}>
                            <select
                                id="purchase_order_id"
                                className={`select ${errors.purchase_order_id ? 'invalid' : ''}`}
                                value={data.purchase_order_id}
                                onChange={(event) => pilihPO(event.target.value)}
                            >
                                <option value="">— pilih PO —</option>
                                {orders.map((order) => (
                                    <option key={order.id} value={order.id}>{order.nomor} — {order.supplier}</option>
                                ))}
                            </select>
                        </FormField>
                        <FormField label="Gudang tujuan" name="warehouse_id" error={errors.warehouse_id}>
                            <select
                                id="warehouse_id"
                                className={`select ${errors.warehouse_id ? 'invalid' : ''}`}
                                value={data.warehouse_id}
                                onChange={(event) => setData('warehouse_id', event.target.value)}
                            >
                                <option value="">— pilih gudang —</option>
                                {warehouses.map((warehouse) => (
                                    <option key={warehouse.id} value={warehouse.id}>{warehouse.kode} — {warehouse.nama}</option>
                                ))}
                            </select>
                        </FormField>
                        <FormField label="Tanggal" name="tanggal" error={errors.tanggal}>
                            <input
                                id="tanggal"
                                type="date"
                                className={`input ${errors.tanggal ? 'invalid' : ''}`}
                                value={data.tanggal}
                                onChange={(event) => setData('tanggal', event.target.value)}
                            />
                        </FormField>
                    </div>
                    {poTerpilih && (
                        <p className="small muted" style={{ marginTop: '8px' }}>Supplier: {poTerpilih.supplier ?? '—'}</p>
                    )}
                    <div className="field" style={{ marginTop: '12px' }}>
                        <label htmlFor="catatan">Catatan</label>
                        <textarea
                            id="catatan"
                            className={`textarea ${errors.catatan ? 'invalid' : ''}`}
                            value={data.catatan}
                            onChange={(event) => setData('catatan', event.target.value)}
                        />
                        {errors.catatan && <span className="error">{errors.catatan}</span>}
                    </div>
                </div>

                <div className="panel">
                    <ItemRows
                        columns={columns}
                        rows={data.items}
                        onChange={ubahBaris}
                        onRemove={hapusBaris}
                        errors={errors}
                    />
                    {errors.items && <div className="error" style={{ padding: '0 12px 12px' }}>{errors.items}</div>}
                    <div className="panel-pad row" style={{ justifyContent: 'space-between' }}>
                        <span className="muted">Total diterima: <Num value={total} /></span>
                    </div>
                </div>

                <div className="panel panel-pad no-print row form-actions">
                    <button className="btn btn-primary" type="submit" disabled={processing || !data.purchase_order_id || !data.warehouse_id || data.items.length === 0}>
                        Simpan penerimaan
                    </button>
                    <Link className="btn" href="/goods-receipts">Kembali</Link>
                </div>
            </form>
        </Layout>
    );
}
