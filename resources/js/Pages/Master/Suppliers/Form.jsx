import { Head, useForm } from '@inertiajs/react';
import FormField from '../../../Components/FormField';
import Layout from '../../../Components/Layout';

export default function SupplierForm({ supplier }) {
    const editing = Boolean(supplier);
    const { data, setData, post, put, processing, errors } = useForm({
        kode: supplier?.kode ?? '',
        nama: supplier?.nama ?? '',
        kontak: supplier?.kontak ?? '',
        telepon: supplier?.telepon ?? '',
        alamat: supplier?.alamat ?? '',
        aktif: supplier?.aktif ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true };
        editing ? put(`/suppliers/${supplier.id}`, options) : post('/suppliers', options);
    };

    return (
        <Layout title={editing ? 'Ubah supplier' : 'Tambah supplier'}>
            <Head title={editing ? 'Ubah supplier' : 'Tambah supplier'} />
            <form className="panel panel-pad" onSubmit={submit}>
                <div className="form-grid">
                    <FormField label="Kode" name="kode" error={errors.kode}>
                        <input id="kode" className={`input ${errors.kode ? 'invalid' : ''}`} value={data.kode} onChange={(e) => setData('kode', e.target.value)} />
                    </FormField>
                    <FormField label="Nama" name="nama" error={errors.nama}>
                        <input id="nama" className={`input ${errors.nama ? 'invalid' : ''}`} value={data.nama} onChange={(e) => setData('nama', e.target.value)} />
                    </FormField>
                    <FormField label="Kontak" name="kontak" error={errors.kontak}>
                        <input id="kontak" className="input" value={data.kontak} onChange={(e) => setData('kontak', e.target.value)} />
                    </FormField>
                    <FormField label="Telepon" name="telepon" error={errors.telepon}>
                        <input id="telepon" className="input" value={data.telepon} onChange={(e) => setData('telepon', e.target.value)} />
                    </FormField>
                    <FormField label="Alamat" name="alamat" error={errors.alamat}>
                        <textarea id="alamat" className="textarea" value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} />
                    </FormField>
                    <label className="row"><input type="checkbox" checked={data.aktif} onChange={(e) => setData('aktif', e.target.checked)} /> Aktif</label>
                </div>
                <div className="form-actions row">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan</button>
                    <a className="btn" href="/suppliers">Kembali</a>
                </div>
            </form>
        </Layout>
    );
}
