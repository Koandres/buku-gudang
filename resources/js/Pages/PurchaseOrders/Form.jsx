import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import FormField from '../../Components/FormField';
import ItemRows from '../../Components/ItemRows';
import { Rupiah } from '../../Components/Num';

function barisDariPR(pr) {
    return (pr?.items ?? [])
        .filter((line) => Number(line.sisa) > 0)
        .map((line) => ({
            purchase_request_item_id: String(line.id),
            kode: line.kode ?? '',
            nama: line.nama,
            satuan: line.satuan ?? '',
            sisa: String(line.sisa),
            jumlah: String(line.sisa),
            harga: String(line.harga_acuan ?? 0),
        }));
}

export default function OrderForm({ order, requests, selectedRequest, suppliers, tanggal }) {
    const awal = requests.find((pr) => String(pr.id) === String(selectedRequest)) ?? requests[0] ?? null;
    const { data, setData, post, processing, errors } = useForm({
        tanggal: order?.tanggal ?? tanggal,
        supplier_id: order?.supplier_id ? String(order.supplier_id) : (suppliers[0] ? String(suppliers[0].id) : ''),
        purchase_request_id: awal ? String(awal.id) : '',
        catatan: order?.catatan ?? '',
        items: barisDariPR(awal),
    });

    const pilihPR = (id) => {
        const pr = requests.find((item) => String(item.id) === String(id));
        setData({
            ...data,
            purchase_request_id: id ? String(id) : '',
            items: barisDariPR(pr),
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
        post('/purchase-orders');
    };

    const columns = [
        { key: 'purchase_request_item_id', label: '', type: 'hidden' },
        { key: 'nama', label: 'Barang', readOnly: true, displayKey: 'nama' },
        { key: 'kode', label: 'Kode', readOnly: true },
        { key: 'satuan', label: 'Satuan', readOnly: true },
        { key: 'sisa', label: 'Sisa PR', align: 'num', readOnly: true },
        { key: 'jumlah', label: 'Jumlah', type: 'number', align: 'num' },
        { key: 'harga', label: 'Harga satuan', type: 'number', align: 'num' },
    ];

    const total = data.items.reduce(
        (sum, row) => sum + (Number(row.jumlah) || 0) * (Number(row.harga) || 0),
        0,
    );
    const prTerpilih = requests.find((pr) => String(pr.id) === String(data.purchase_request_id));

    return (
        <Layout title="Buat Pesanan Pembelian" subtitle="Buat PO dari PR yang sudah disetujui">
            <Head title="Buat PO" />
            {requests.length === 0 && (
                <div className="flash error">Belum ada PR disetujui dengan sisa barang yang bisa dipesan.</div>
            )}
            <form onSubmit={submit} className="stack">
                <div className="panel panel-pad">
                    <div className="form-grid">
                        <FormField label="Dari PR" name="purchase_request_id" error={errors.purchase_request_id}>
                            <select
                                id="purchase_request_id"
                                className={`select ${errors.purchase_request_id ? 'invalid' : ''}`}
                                value={data.purchase_request_id}
                                onChange={(event) => pilihPR(event.target.value)}
                            >
                                <option value="">— pilih PR —</option>
                                {requests.map((pr) => (
                                    <option key={pr.id} value={pr.id}>{pr.nomor} — {pr.keperluan}</option>
                                ))}
                            </select>
                        </FormField>
                        <FormField label="Supplier" name="supplier_id" error={errors.supplier_id}>
                            <select
                                id="supplier_id"
                                className={`select ${errors.supplier_id ? 'invalid' : ''}`}
                                value={data.supplier_id}
                                onChange={(event) => setData('supplier_id', event.target.value)}
                            >
                                <option value="">— pilih supplier —</option>
                                {suppliers.map((supplier) => (
                                    <option key={supplier.id} value={supplier.id}>{supplier.kode} — {supplier.nama}</option>
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
                    {prTerpilih && (
                        <p className="small muted" style={{ marginTop: '8px' }}>
                            Pemohon: {prTerpilih.pemohon ?? '—'} · {prTerpilih.keperluan}
                        </p>
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
                        <span className="muted">Estimasi: <Rupiah value={total} /></span>
                        <span className="small muted">Jumlah dan harga dalam bilangan bulat.</span>
                    </div>
                </div>

                <div className="panel panel-pad no-print row form-actions">
                    <button className="btn btn-primary" type="submit" disabled={processing || !data.purchase_request_id || data.items.length === 0}>
                        Simpan draft PO
                    </button>
                    <Link className="btn" href="/purchase-orders">Kembali</Link>
                </div>
            </form>
        </Layout>
    );
}
