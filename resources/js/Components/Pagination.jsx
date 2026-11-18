import { Link } from '@inertiajs/react';

export default function Pagination({ meta }) {
    if (!meta || meta.last_page <= 1) {
        return null;
    }

    return (
        <nav className="pagination" aria-label="Halaman">
            {meta.links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        className={link.active ? 'current' : ''}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={index} dangerouslySetInnerHTML={{ __html: link.label }} />
                ),
            )}
        </nav>
    );
}
