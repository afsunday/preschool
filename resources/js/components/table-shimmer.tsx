export const TableShimmer = ({ className = '' }: { className?: string }) => {
    const shimmerRows = Array.from({ length: 10 });

    return (
        <div className={`mt-5 mb-5 w-full ${className}`}>
            <div className="w-full rounded-md border">
                {/* Table Header */}
                <div className="flex items-center space-x-4 rounded-t-lg bg-[#F6F9FC] px-4 py-5">
                    {Array.from({ length: 5 }).map((_, index) => (
                        <div
                            key={index}
                            className="h-4 w-1/5 animate-pulse rounded bg-gray-200"
                        ></div>
                    ))}
                </div>

                {/* Table Body */}
                <div className="flex flex-col space-y-4 p-4">
                    {shimmerRows.map((_, index) => (
                        <div
                            key={index}
                            className="flex items-center space-x-4 py-0.5"
                        >
                            {Array.from({ length: 5 }).map((_, colIndex) => (
                                <div
                                    key={colIndex}
                                    className="h-4 w-1/5 animate-pulse rounded bg-gray-200"
                                ></div>
                            ))}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};
