import { Head, useForm } from '@inertiajs/react';
import FormField from '../../Components/FormField';

export default function Login({ appName }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Masuk" />
            <div className="content" style={{ maxWidth: '420px', margin: '10vh auto' }}>
                <div className="page-head">
                    <div>
                        <h1 className="page-title">{appName}</h1>
                        <div className="page-sub">Masuk untuk melanjutkan</div>
                    </div>
                </div>
                <form className="panel panel-pad stack" onSubmit={submit}>
                    <FormField label="Email" name="email" error={errors.email}>
                        <input
                            id="email"
                            className={`input ${errors.email ? 'invalid' : ''}`}
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            autoComplete="username"
                            required
                        />
                    </FormField>
                    <FormField label="Kata sandi" name="password" error={errors.password}>
                        <input
                            id="password"
                            className={`input ${errors.password ? 'invalid' : ''}`}
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="current-password"
                            required
                        />
                    </FormField>
                    <label className="row small">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(event) => setData('remember', event.target.checked)}
                        />
                        Ingat saya
                    </label>
                    <div className="form-actions">
                        <button className="btn btn-primary" type="submit" disabled={processing}>
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}
