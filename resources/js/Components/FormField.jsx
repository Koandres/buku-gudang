export default function FormField({ label, name, error, children, hint }) {
    return (
        <div className="field">
            <label htmlFor={name}>{label}</label>
            {children}
            {hint && <span className="small muted">{hint}</span>}
            {error && <span className="error">{error}</span>}
        </div>
    );
}
