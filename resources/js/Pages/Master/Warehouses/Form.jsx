import { Head, useForm } from '@inertiajs/react';
import FormField from '../../../Components/FormField';
import Layout from '../../../Components/Layout';

export default function WarehouseForm({ warehouse }) {
    const editing = Boolean(warehouse);
    const { data, setData, post, put, processing, errors } = useForm({
        kode: warehouse?.kode ?? '',
        nama: warehouse?.nama ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true };
        editing ? put(`/warehouses/${warehouse.id}`, options) : post('/warehouses', options);
    };

    return (
        <Layout title={editing ? 'Ubah gudang' : 'Tambah gudang'}>
            <Head title={editing ? 'Ubah gudang' : 'Tambah gudang'} />
            <form className="panel panel-pad" onSubmit={submit}>
                <div className="form-grid">
                    <FormField label="Kode" name="kode" error={errors.kode}>
                        <input id="kode" className={`input ${errors.kode ? 'invalid' : ''}`} value={data.kode} onChange={(e) => setData('kode', e.target.value)} />
                    </FormField>
                    <FormField label="Nama" name="nama" error={errors.nama}>
                        <input id="nama" className={`input ${errors.nama ? 'invalid' : ''}`} value={data.nama} onChange={(e) => setData('nama', e.target.value)} />
                    </FormField>
                </div>
                <div className="form-actions row">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan</button>
                    <a className="btn" href="/warehouses">Kembali</a>
                </div>
            </form>
        </Layout>
    );
}
