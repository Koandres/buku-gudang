import { Head, useForm } from '@inertiajs/react';
import FormField from '../../../Components/FormField';
import Layout from '../../../Components/Layout';

export default function ItemForm({ item, units }) {
    const editing = Boolean(item);
    const { data, setData, post, put, processing, errors } = useForm({
        kode: item?.kode ?? '',
        nama: item?.nama ?? '',
        satuan: item?.satuan ?? units[0],
        harga_acuan: item?.harga_acuan ?? 0,
        stok_minimum: item?.stok_minimum ?? 0,
        aktif: item?.aktif ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true };
        editing ? put(`/items/${item.id}`, options) : post('/items', options);
    };

    return (
        <Layout title={editing ? 'Ubah barang' : 'Tambah barang'}>
            <Head title={editing ? 'Ubah barang' : 'Tambah barang'} />
            <form className="panel panel-pad" onSubmit={submit}>
                <div className="form-grid">
                    <FormField label="Kode" name="kode" error={errors.kode}>
                        <input id="kode" className={`input ${errors.kode ? 'invalid' : ''}`} value={data.kode} onChange={(e) => setData('kode', e.target.value)} />
                    </FormField>
                    <FormField label="Nama" name="nama" error={errors.nama}>
                        <input id="nama" className={`input ${errors.nama ? 'invalid' : ''}`} value={data.nama} onChange={(e) => setData('nama', e.target.value)} />
                    </FormField>
                    <FormField label="Satuan" name="satuan" error={errors.satuan}>
                        <select id="satuan" className="select" value={data.satuan} onChange={(e) => setData('satuan', e.target.value)}>
                            {units.map((unit) => <option key={unit} value={unit}>{unit}</option>)}
                        </select>
                    </FormField>
                    <FormField label="Harga acuan (Rp)" name="harga_acuan" error={errors.harga_acuan}>
                        <input id="harga_acuan" type="number" min="0" className={`input num ${errors.harga_acuan ? 'invalid' : ''}`} value={data.harga_acuan} onChange={(e) => setData('harga_acuan', e.target.value)} />
                    </FormField>
                    <FormField label="Stok minimum" name="stok_minimum" error={errors.stok_minimum}>
                        <input id="stok_minimum" type="number" min="0" className={`input num ${errors.stok_minimum ? 'invalid' : ''}`} value={data.stok_minimum} onChange={(e) => setData('stok_minimum', e.target.value)} />
                    </FormField>
                    <label className="row"><input type="checkbox" checked={data.aktif} onChange={(e) => setData('aktif', e.target.checked)} /> Aktif</label>
                </div>
                <div className="form-actions row">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan</button>
                    <a className="btn" href="/items">Kembali</a>
                </div>
            </form>
        </Layout>
    );
}
