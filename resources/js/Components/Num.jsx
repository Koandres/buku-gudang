export default function Num({ value, suffix = '' }) {
    return (
        <span className="num">
            {Number(value ?? 0).toLocaleString('id-ID')}
            {suffix}
        </span>
    );
}

export function Rupiah({ value }) {
    return <Num value={value} suffix=",-" />;
}
