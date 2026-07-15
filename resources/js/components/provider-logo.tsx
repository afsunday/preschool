import { useEffect, useState } from 'react';

/** Brand fallbacks when a provider's logo file isn't present. */
const BRAND: Record<string, { bg: string; label: string }> = {
    squad_pos: { bg: '#EF4A24', label: 'S' },
    opay_pos: { bg: '#12CE8F', label: 'O' },
    moniepoint_pos: { bg: '#0361F0', label: 'M' },
};

/**
 * POS provider logo. Loads /icons/pos/{provider}.svg (drop official logos there),
 * and falls back to a brand-coloured initial badge if the file is missing.
 */
export default function ProviderLogo({ provider, className = 'h-9 w-9' }: { provider: string; className?: string }) {
    const [failed, setFailed] = useState(false);
    const brand = BRAND[provider] ?? { bg: '#3f3f46', label: provider.charAt(0).toUpperCase() };

    // Reset the error state if the provider changes.
    useEffect(() => setFailed(false), [provider]);

    if (failed) {
        return (
            <span className={`grid flex-shrink-0 place-content-center rounded-lg font-bold text-white ${className}`} style={{ backgroundColor: brand.bg }}>
                {brand.label}
            </span>
        );
    }

    return (
        <img
            src={`/icons/${provider}.png`}
            alt={provider}
            onError={() => setFailed(true)}
            className={`flex-shrink-0 rounded-lg object-contain ${className}`}
        />
    );
}
