/**
 * Minimal className joiner — vendored inside the CMS folder so it carries no
 * dependency on the host app's `@/lib/utils`. Keeps this folder copy-pasteable.
 */
export function cn(
    ...parts: Array<string | false | null | undefined>
): string {
    return parts.filter(Boolean).join(' ');
}
