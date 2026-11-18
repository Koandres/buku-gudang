export default function StatusBadge({ status, label, tone = 'netral' }) {
    return <span className={`status tone-${tone}`}>{label ?? status}</span>;
}
