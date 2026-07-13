{{--
 | Full-bleed cloud/blob image — the exact path exported from Figma.
 | Shared by the About hero and the Forms & Policies hero.
 |
 | Props: $src, $alt, $id (unique clipPath id — two on one page would collide)
 |
 | The path is used as a CLIP-PATH in the viewBox's own coordinate space
 | (userSpaceOnUse). Filling it via a <pattern> distorts the photo: an
 | objectBoundingBox pattern gives the <image> a 1x1 *square* viewport, so
 | `slice` crops to a square and the wide bbox then stretches it.
 | The image is drawn oversized because the path bleeds outside the viewBox
 | (x: -80 → 1994).
--}}
@props(['src', 'alt' => '', 'id' => 'wodi-cloud'])

<div class="relative mx-auto w-full max-w-[1600px]">
    <svg viewBox="0 0 1920 517"
         role="img" aria-label="{{ $alt }}"
         class="block h-auto w-full"
         xmlns="http://www.w3.org/2000/svg">
        <defs>
            <clipPath id="{{ $id }}" clipPathUnits="userSpaceOnUse">
                <path d="M337.024 379.431C373.91 432.803 450.51 393.799 450.51 393.799C450.51 393.799 461.859 486.175 617.9 502.598C773.941 519.021 842.032 441.015 842.032 441.015C952.679 519.022 1023.6 438.96 1023.6 438.96C1023.6 438.96 1094.52 514.912 1270.43 516.967C1446.33 519.022 1466.19 424.591 1466.19 424.591C1565.48 486.175 1605.2 416.154 1605.2 416.154C1749.89 479.561 1741.38 389.697 1741.38 389.697C1741.38 389.697 1840.68 449.226 1917.28 389.697C1993.88 330.168 1903.09 256.263 1903.09 256.263C1769 154.058 1659.6 209.442 1659.6 209.442C1693.93 133.743 1579.51 124.286 1579.51 124.286C1579.51 124.286 1541.91 3.64487 1419.31 0.0984569C1296.72 -3.44795 1275.47 89.9902 1275.47 89.9902C1275.47 89.9902 1179.03 53.3229 1128.35 73.4333C1077.68 93.5437 1067.88 140.85 1067.88 140.85C1067.88 140.85 974.705 -10.5408 834.126 24.9374C693.547 60.4226 687.013 150.307 687.013 150.307C517.017 62.7799 445.094 195.25 445.094 195.25C378.072 112.458 306.149 195.25 306.149 195.25C218.627 67.0089 122.335 76.4097 68.2075 96.6468C48.943 131.449 58.9984 163.881 58.9984 163.881C-6.25367 165.935 -80.0149 213.151 -43.1393 282.946C-6.25388 352.741 121.411 317.847 121.411 317.847C161.126 455.383 337.024 379.431 337.024 379.431Z" />
            </clipPath>
        </defs>

        <image href="{{ $src }}"
               x="-90" y="-10" width="2090" height="540"
               preserveAspectRatio="xMidYMid slice"
               clip-path="url(#{{ $id }})" />
    </svg>
</div>
