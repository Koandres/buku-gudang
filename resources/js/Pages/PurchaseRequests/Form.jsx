import { Head, useForm } from '@inertiajs/react';
import Layout from '../../Components/Layout';
import FormField from '../../Components/FormField';
import ItemRows from '../../Components/ItemRows';
import Num, { Rupiah } from '../../Components/Num';

export default function RequestForm({ request, items, tanggal }) {
    const editing = Boolean(request);
    const { data, setData, post, put, processing, errors } = useForm({
        tanggal: request?.tanggal ?? tanggal,
        keperluan: request?.keperluan ?? '',
        items: request?.items?.map((line) => ({
            item_id: String(line.item_id),
            jumlah: String(line.jumlah),
        })) ?? [{ item_id: '', jumlah: '1' }],
    });

    const columns = [
        {
            key: 'item_id', label: 'Barang', type: 'select',
            options: items.map((item) => ({
                value: String(item.id),
                label: `${item.kode} — ${item.nama} (${item.satuan})`,
            })),
        },
        { key: 'jumlah', label: 'Jumlah', type: 'number', align: 'num', min: 1 },
        {
            key: 'harga', label: 'Harga acuan', align: 'num', readOnly: true,
            render: (row) => <Rupiah value={items.find((item) => String(item.id) === String(row.item_id))?.harga_acuan ?? 0} />,
        },
    ];

    const changeItem = (index, key, value) => {
        const next = [...data.items];
        next[index] = { ...next[index], [key]: value };
        setData('items', next);
    };

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true };
        editing ? put(`/purchase-requests/${request.id}`, options) : post('/purchase-requests', options);
    };

    const total = data.items.reduce((sum, line) => {
        const item = items.find((entry) => String(entry.id) === String(line.item_id));
        return sum + (item?.harga_acuan ?? 0) * Number(line.jumlah || 0);
    }, 0);

    return (
        <Layout title={editing ? `Ubah ${request.nomor}` : 'Buat permintaan pembelian'}>
            <Head title={editing ? 'Ubah PR' : 'Buat PR'} />
            <form className="stack" onSubmit={submit}>
                <div className="panel panel-pad">
                    <div className="form-grid">
                        <FormField label="Nomor" name="nomor">
                            <input id="nomor" className="input" value={request?.nomor ?? 'Otomatis setelah disimpan'} disabled />
                        </FormField>
                        <FormField label="Tanggal" name="tanggal" error={errors.tanggal}>
                            <input id="tanggal" type="date" className={`input ${errors.tanggal ? 'invalid' : ''}`} value={data.tanggal} onChange={(e) => setData('tanggal', e.target.value)} />
                        </FormField>
                    </div>
                    <div className="form-grid" style={{ marginTop: '12px' }}>
                        <FormField label="Keperluan" name="keperluan" error={errors.keperluan}>
                            <textarea id="keperluan" className={`textarea ${errors.keperluan ? 'invalid' : ''}`} value={data.keperluan} onChange={(e) => setData('keperluan', e.target.value)} />
                        </FormField>
                    </div>
                </div>
                <div className="panel">
                    <ItemRows
                        columns={columns}
                        rows={data.items}
                        onChange={changeItem}
                        onRemove={data.items.length > 1 ? (index) => setData('items', data.items.filter((_, i) => i !== index)) : null}
                        errors={errors}
                    />
                    <div className="row" style={{ padding: '12px', justifyContent: 'space-between' }}>
                        <button type="button" className="btn" onClick={() => setData('items', [...data.items, { item_id: '', jumlah: '1' }])}>Tambah baris</button>
                        <span className="small muted">Estimasi: <Rupiah value={total} /></span>
                    </div>
                </div>
                <div className="form-actions row">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan draft</button>
                    <a className="btn" href="/purchase-requests">Kembali</a>
                </div>
            </form>
        </Layout>
    );
}
