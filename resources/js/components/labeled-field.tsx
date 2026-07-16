type fieldProps = React.InputHTMLAttributes<HTMLInputElement> & {
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    label: string;
    divClassName?: string;
};

export default function LabeledField({
    onChange,
    label,
    divClassName,
    ...props
}: fieldProps) {
    const id = label.replace(/\s+/g, '-').toLowerCase();

    return (
        <div className={divClassName}>
            <label htmlFor={id} className="mb-1 block text-sm">
                {label}
            </label>
            <input
                id={id}
                placeholder="Jeremy"
                className="form-control bg-transparent"
                onChange={(e) => onChange(e)}
                {...props}
            />
        </div>
    );
}
