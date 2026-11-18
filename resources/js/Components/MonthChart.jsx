export default function MonthChart({ data }) {
    const max = Math.max(1, ...data.map((item) => item.total));
    const height = 160;
    const width = 600;
    const barWidth = 56;
    const step = width / Math.max(data.length, 1);

    return (
        <svg viewBox={`0 0 ${width} ${height + 28}`} role="img" aria-label="Nilai pembelian enam bulan terakhir">
            <line x1="0" y1={height} x2={width} y2={height} style={{ stroke: 'var(--color-line)' }} />
            {data.map((item, index) => {
                const barHeight = Math.round((item.total / max) * (height - 16));
                return (
                    <g key={item.label}>
                        <rect
                            x={index * step + (step - barWidth) / 2}
                            y={height - barHeight}
                            width={barWidth}
                            height={barHeight}
                            style={{ fill: 'var(--color-accent)' }}
                        />
                        <text
                            x={index * step + step / 2}
                            y={height + 18}
                            textAnchor="middle"
                            className="small"
                            style={{ fill: 'var(--color-muted)' }}
                        >
                            {item.label}
                        </text>
                    </g>
                );
            })}
        </svg>
    );
}
