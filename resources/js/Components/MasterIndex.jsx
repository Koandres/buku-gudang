import { Link } from '@inertiajs/react';
import Layout from './Layout';
import DataTable from './DataTable';
import Pagination from './Pagination';

export default function MasterIndex({ title, subtitle, href, columns, rows, meta, q, canManage, createLabel, children }) {
    return (
        <Layout
            title={title}
            subtitle={subtitle}
            actions={canManage && <Link className="btn btn-primary" href={`${href}/create`}>{createLabel}</Link>}
        >
            <form className="toolbar" method="get">
                <div className="field">
                    <label htmlFor="q">Cari</label>
                    <input id="q" name="q" className="input" defaultValue={q} placeholder="Kode atau nama" />
                </div>
                <button className="btn" type="submit">Cari</button>
            </form>
            <div className="panel">
                <DataTable columns={columns} rows={rows} />
                <Pagination meta={meta} />
            </div>
            {children}
        </Layout>
    );
}
