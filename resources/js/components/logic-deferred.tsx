import { ReactElement, useEffect, useState } from 'react';

interface DeferredProps {
    children: ReactElement | number | string;
    fallback: ReactElement | number | string;
    loading: boolean;
}

const LogicDeferred = ({ children, fallback, loading }: DeferredProps) => {
    const [loaded, setLoaded] = useState(false);

    useEffect(() => {
        setLoaded(!loading);
    }, [loading]);

    return loaded ? children : fallback;
};

export default LogicDeferred;
