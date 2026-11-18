import { Head, useForm } from '@inertiajs/react';
import FormField from '../../../Components/FormField';
import Layout from '../../../Components/Layout';

export default function UserForm({ user, roles }) {
    const editing = Boolean(user);
    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        role: user?.role ?? 'pemohon',
        active: user?.active ?? true,
        password: '',
    });

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true };
        editing ? put(`/users/${user.id}`, options) : post('/users', options);
    };

    return (
        <Layout title={editing ? 'Ubah pengguna' : 'Tambah pengguna'}>
            <Head title={editing ? 'Ubah pengguna' : 'Tambah pengguna'} />
            <form className="panel panel-pad" onSubmit={submit}>
                <div className="form-grid">
                    <FormField label="Nama" name="name" error={errors.name}>
                        <input id="name" className={`input ${errors.name ? 'invalid' : ''}`} value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </FormField>
                    <FormField label="Email" name="email" error={errors.email}>
                        <input id="email" type="email" className={`input ${errors.email ? 'invalid' : ''}`} value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </FormField>
                    <FormField label="Peran" name="role" error={errors.role}>
                        <select id="role" className="select" value={data.role} onChange={(e) => setData('role', e.target.value)}>
                            {roles.map((role) => <option key={role.value} value={role.value}>{role.label}</option>)}
                        </select>
                    </FormField>
                    <FormField label={editing ? 'Kata sandi baru (kosongkan jika tidak diubah)' : 'Kata sandi'} name="password" error={errors.password}>
                        <input id="password" type="password" className={`input ${errors.password ? 'invalid' : ''}`} value={data.password} onChange={(e) => setData('password', e.target.value)} />
                    </FormField>
                    <label className="row"><input type="checkbox" checked={data.active} onChange={(e) => setData('active', e.target.checked)} /> Aktif</label>
                </div>
                <div className="form-actions row">
                    <button className="btn btn-primary" type="submit" disabled={processing}>Simpan</button>
                    <a className="btn" href="/users">Kembali</a>
                </div>
            </form>
        </Layout>
    );
}
