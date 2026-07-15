import { RotateCw } from 'lucide-react';
import React, { useState } from 'react';

type ReloadButtonProps = React.HTMLAttributes<HTMLSpanElement> & {
    reload: () => void;
    duration?: number;
};

const ReloadButton: React.FC<ReloadButtonProps> = ({ reload, duration = 1000, className = '', ...props }) => {
    const [isSpinning, setIsSpinning] = useState(false);

    const handleReload = () => {
        setIsSpinning(true);
        reload();

        setTimeout(() => {
            setIsSpinning(false);
        }, duration);
    };

    return (
        <button {...props} className={`btn-light cursor-pointer p-2 px-3 py-[9px] !shadow-sm hover:!shadow-none ${className}`} onClick={handleReload}>
            <dt className={`h-max w-max ${isSpinning ? 'animate-spin' : ''}`}>
                <RotateCw className="size-4" />
            </dt>
        </button>
    );
};

export default ReloadButton;
