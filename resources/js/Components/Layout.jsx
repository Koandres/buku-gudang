import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const keuangan = ['pembelian', 'admin'];
const kartuStok = ['gudang', 'pembelian', 'admin'];

const groups = [
    { title: 'Ringkasan', links: [['/dashboard', 'Dashboard', keuangan]] },
    {
        title: 'Pengadaan',
        links: [
            ['/purchase-requests', 'Permintaan Pembelian'],
            ['/purchase-orders', 'Pesanan Pembelian'],
            ['/goods-receipts', 'Penerimaan Barang'],
        ],
    },
    {
        title: 'Gudang',
        links: [
            ['/stock', 'Kartu Stok', kartuStok],
            ['/stock/adjust', 'Penyesuaian Stok', ['gudang', 'admin']],
        ],
    },
    {
        title: 'Laporan',
        links: [
            ['/reports', 'Ringkasan', keuangan],
            ['/reports/outstanding', 'PO Outstanding', keuangan],
            ['/reports/suppliers', 'Per Supplier', keuangan],
            ['/reports/stock', 'Stok per Gudang'],
            ['/reports/reorder', 'Perlu Dipesan', keuangan],
        ],
    },
    {
        title: 'Master',
        links: [
            ['/suppliers', 'Supplier'],
            ['/items', 'Barang'],
            ['/warehouses', 'Gudang'],
            ['/users', 'Pengguna'],
        ],
    },
    { title: 'Akun', links: [['/profile/password', 'Ubah Kata Sandi']] },
    { title: 'Admin', links: [['/activities', 'Log Aktivitas']], roles: ['admin'] },
];

export default function Layout({ children, title, subtitle, actions }) {
    const page = usePage();
    const { auth, flash, appName } = page.props;
    const url = page.url ?? window.location.pathname;
    const user = auth?.user;
    const [theme, setTheme] = useState(() => document.documentElement.dataset.theme || 'light');

    const toggleTheme = () => {
        const next = theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('buku-gudang-theme', next);
        setTheme(next);
    };

    return (
        <div className="app-shell">
            <aside className="sidebar no-print">
                <div className="brand">
                    {appName}
                    <small>Buku Besar</small>
                </div>
                <div className="nav-groups">
                    {groups
                        .filter((group) => (!group.roles || group.roles.includes(user?.role))
                            && group.links.some(([, , roles]) => !roles || roles.includes(user?.role)))
                        .map((group) => (
                            <nav className="nav-group" key={group.title} aria-label={group.title}>
                                <div className="nav-group-title">{group.title}</div>
                                {group.links
                                    .filter(([, , roles]) => !roles || roles.includes(user?.role))
                                    .map(([href, label]) => (
                                        <Link
                                            key={href}
                                            href={href}
                                            className={`nav-link ${url.startsWith(href) ? 'active' : ''}`}
                                        >
                                            {label}
                                        </Link>
                                    ))}
                            </nav>
                        ))}
                </div>
            </aside>
            <div className="main">
                <header className="topbar no-print">
                    <span className="small muted">{user ? `${user.name} · ${user.roleLabel}` : ''}</span>
                    <span className="row">
                        <button type="button" className="btn" onClick={toggleTheme}>
                            {theme === 'dark' ? 'Tema terang' : 'Tema gelap'}
                        </button>
                        {user && (
                            <Link href="/logout" method="post" as="button" className="btn">
                                Keluar
                            </Link>
                        )}
                    </span>
                </header>
                <main className="content">
                    {flash?.success && <div className="flash">{flash.success}</div>}
                    {flash?.error && <div className="flash error">{flash.error}</div>}
                    {title && (
                        <div className="page-head">
                            <div>
                                <h1 className="page-title">{title}</h1>
                                {subtitle && <div className="page-sub">{subtitle}</div>}
                            </div>
                            {actions && <div className="row no-print">{actions}</div>}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}
