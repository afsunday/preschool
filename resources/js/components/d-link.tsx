import type { AnchorHTMLAttributes, PropsWithChildren } from 'react';

export default function Dlink({
    url,
    fileName,
    children,
    ...props
}: PropsWithChildren<{
    url: string;
    fileName: string;
}> &
    AnchorHTMLAttributes<HTMLAnchorElement>) {
    const handleDownload = () => {
        fetch(url)
            .then((response) => response.blob())
            .then((blob) => {
                const objectUrl = window.URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = fileName || 'downloaded-file';
                document.body.appendChild(link);

                link.click();

                document.body.removeChild(link);
                window.URL.revokeObjectURL(objectUrl);
            })
            .catch((error) => {
                console.error('Error fetching the file:', error);
            });
    };

    return (
        <a {...props} onClick={handleDownload}>
            {children}
        </a>
    );
}
