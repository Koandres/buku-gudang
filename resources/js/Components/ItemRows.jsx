import Num from './Num';

export default function ItemRows({ columns, rows, onChange, errors = {}, readOnly = false, onRemove = null }) {
    return (
        <div className="table-wrap">
            <table className="ledger">
                <thead>
                    <tr>
                        <th className="num">#</th>
                        {columns.map((column) => (
                            <th key={column.key} className={column.align === 'num' ? 'num' : ''}>
                                {column.label}
                            </th>
                        ))}
                        {onRemove && !readOnly && <th>Aksi</th>}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row, index) => (
                        <tr className="item-row" key={index}>
                            <td className="num muted">{index + 1}</td>
                            {columns.map((column) => (
                                <td key={column.key} className={column.align === 'num' ? 'num' : ''}>
                                    {readOnly || column.readOnly ? (
                                        column.render ? (
                                            column.render(row)
                                        ) : column.align === 'num' ? (
                                            <Num value={row[column.key]} />
                                        ) : (
                                            row[column.displayKey ?? column.key]
                                        )
                                    ) : column.type === 'hidden' ? (
                                        <input type="hidden" value={row[column.key] ?? ''} readOnly />
                                    ) : column.type === 'select' ? (
                                        <select
                                            className={`select ${errors[`items.${index}.${column.key}`] ? 'invalid' : ''}`}
                                            value={row[column.key] ?? ''}
                                            onChange={(event) => onChange(index, column.key, event.target.value)}
                                        >
                                            <option value="">— pilih —</option>
                                            {column.options.map((option) => (
                                                <option key={option.value} value={option.value}>
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    ) : (
                                        <input
                                            className={`input ${column.align === 'num' ? 'num' : ''} ${errors[`items.${index}.${column.key}`] ? 'invalid' : ''}`}
                                            type={column.type === 'number' ? 'number' : 'text'}
                                            min={column.type === 'number' ? (column.min ?? 1) : undefined}
                                            value={row[column.key] ?? ''}
                                            onChange={(event) => onChange(index, column.key, event.target.value)}
                                        />
                                    )}
                                    {errors[`items.${index}.${column.key}`] && (
                                        <span className="error">{errors[`items.${index}.${column.key}`]}</span>
                                    )}
                                </td>
                            ))}
                            {onRemove && !readOnly && (
                                <td>
                                    <button type="button" className="btn" onClick={() => onRemove(index)}>Hapus</button>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
