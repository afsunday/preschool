import type { ReactElement } from 'react';

interface DeferredProps {
    children: ReactElement | number | string;
    fallback: ReactElement | number | string;
    loading: boolean;
}

const LogicDeferred = ({ children, fallback, loading }: DeferredProps) => {
    return loading ? fallback : children;
};

export default LogicDeferred;
