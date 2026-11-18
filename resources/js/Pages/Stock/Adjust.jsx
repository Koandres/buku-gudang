import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import FormField from '../../Components/FormField';
import Num from '../../Components/Num';

export default function StockAdjust({ items, warehouses, saldo }) {
    const { data, setData, post, processing, errors } = useForm({
        item_id: items[0] ? String(items[0].id) : '',
        warehouse_id: warehouses[0] ? String(warehouses[0].id) : '',
        quantity: '',
        catatan: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/stock/adjust');
    };

    const saldoSaatIni = saldo?.[data.item_id]?.[data.warehouse_id] ?? 0;

    return (
        <Layout title="Penyesuaian Stok" subtitle="Catat koreksi stok akibat hitung fisik atau hal lain">
            <Head title="Penyesuaian Stok" />
            <form onSubmit={submit} className="stack">
                <div className="panel panel-pad">
                    <div className="form-grid">
                        <FormField label="Barang" name="item_id" error={errors.item_id}>
                            <select
                                id="item_id"
                                className={`select ${errors.item_id ? 'invalid' : ''}`}
                                value={data.item_id}
                                onChange={(event) => setData('item_id', event.target.value)}
                            >
                                <option value="">— pilih barang —</option>
                                {items.map((item) => (
                                    <option key={item.id} value={item.id}>{item.kode} — {item.nama}</option>
                                ))}
                            </select>
                        </FormField>
                        <FormField label="Gudang" name="warehouse_id" error={errors.warehouse_id}>
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
                        <FormField label="Penyesuaian" name="quantity" error={errors.quantity} hint="Angka negatif mengurangi stok.">
                            <input
                                id="quantity"
                                type="number"
                                className={`input ${errors.quantity ? 'invalid' : ''}`}
                                value={data.quantity}
                                onChange={(event) => setData('quantity', event.target.value)}
                                placeholder="Contoh: -3 atau 5"
                            />
                        </FormField>
                    </div>
                    <p className="small muted" style={{ marginTop: '12px' }}>
                        Saldo saat ini: <Num value={saldoSaatIni} />
                    </p>
                    <div className="field" style={{ marginTop: '12px' }}>
                        <label htmlFor="catatan">Alasan penyesuaian</label>
                        <textarea
                            id="catatan"
                            className={`textarea ${errors.catatan ? 'invalid' : ''}`}
                            value={data.catatan}
                            onChange={(event) => setData('catatan', event.target.value)}
                        />
                        {errors.catatan && <span className="error">{errors.catatan}</span>}
                    </div>
                </div>

                <div className="panel panel-pad no-print row form-actions">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan penyesuaian</button>
                    <Link className="btn" href="/stock">Kembali</Link>
                </div>
            </form>
        </Layout>
    );
}
