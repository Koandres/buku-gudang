import { Head } from '@inertiajs/react';
import Layout from './Layout';
import DataTable from './DataTable';

export default function ReportShell({ title, subtitle, jenis, columns, rows, empty }) {
    return (
        <Layout
            title={title}
            subtitle={subtitle}
            actions={<a className="btn" href={`/reports/export?jenis=${jenis}`}>Ekspor CSV</a>}
        >
            <Head title={title} />
            <div className="panel">
                <DataTable columns={columns} rows={rows} empty={empty ?? 'Tidak ada data.'} />
            </div>
        </Layout>
    );
}
