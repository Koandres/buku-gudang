import { Head, useForm } from '@inertiajs/react';
import FormField from '../../Components/FormField';
import Layout from '../../Components/Layout';

export default function Password() {
    const { data, setData, put, processing, errors } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        put('/profile/password');
    };

    return (
        <Layout title="Ubah Kata Sandi" subtitle="Perbarui kata sandi akun Anda">
            <Head title="Ubah Kata Sandi" />
            <form onSubmit={submit} className="stack" style={{ maxWidth: '480px' }}>
                <div className="panel panel-pad stack">
                    <FormField label="Kata sandi saat ini" name="current_password" error={errors.current_password}>
                        <input
                            id="current_password"
                            className={`input ${errors.current_password ? 'invalid' : ''}`}
                            type="password"
                            value={data.current_password}
                            onChange={(event) => setData('current_password', event.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    </FormField>
                    <FormField label="Kata sandi baru" name="password" error={errors.password}>
                        <input
                            id="password"
                            className={`input ${errors.password ? 'invalid' : ''}`}
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="new-password"
                            required
                        />
                    </FormField>
                    <FormField label="Konfirmasi kata sandi baru" name="password_confirmation" error={errors.password_confirmation}>
                        <input
                            id="password_confirmation"
                            className={`input ${errors.password_confirmation ? 'invalid' : ''}`}
                            type="password"
                            value={data.password_confirmation}
                            onChange={(event) => setData('password_confirmation', event.target.value)}
                            autoComplete="new-password"
                            required
                        />
                    </FormField>
                    <div className="form-actions">
                        <button className="btn btn-primary" type="submit" disabled={processing}>
                            Simpan kata sandi
                        </button>
                    </div>
                </div>
            </form>
        </Layout>
    );
}
