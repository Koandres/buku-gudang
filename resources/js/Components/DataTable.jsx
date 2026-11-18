export default function DataTable({ columns, rows, rowKey = 'id', caption, empty = 'Tidak ada data.' }) {
    return (
        <div className="table-wrap">
            <table className="ledger">
                {caption && <caption>{caption}</caption>}
                <thead>
                    <tr>
                        {columns.map((column) => (
                            <th key={column.key} className={column.align === 'num' ? 'num' : ''}>
                                {column.label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {rows.length === 0 && (
                        <tr>
                            <td colSpan={columns.length} className="muted">
                                {empty}
                            </td>
                        </tr>
                    )}
                    {rows.map((row) => (
                        <tr key={row[rowKey]}>
                            {columns.map((column) => (
                                <td key={column.key} className={column.align === 'num' ? 'num' : ''}>
                                    {column.render ? column.render(row) : row[column.key]}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
