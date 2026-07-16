/** Shared shapes for the daycare portal. Children are records, never accounts. */

export interface PortalClass {
    id: number;
    name: string;
    label: string;
    grade: string | null;
    year: string;
    color: string | null;
    banner: string | null;
    teacher: string | null;
    teacherId: number | null;
    childCount: number;
}

export interface PortalGuardian {
    id: number;
    name: string;
    relationship: string | null;
}

export interface PortalChild {
    id: number;
    name: string;
    photo: string | null;
    classroom?: string | null;
    classroomId?: number | null;
    isMine?: boolean;
    guardians: PortalGuardian[];
    inviteCode: string | null;
}

export interface PortalPhoto {
    id: number;
    url: string;
}

export interface PortalPost {
    id: number;
    body: string;
    author: string;
    createdAt: string | null;
    photos: PortalPhoto[];
}

export type ReportEntryType = 'nap' | 'meal' | 'nappy' | 'note' | 'photo';

export interface PortalReportEntry {
    id: number;
    type: ReportEntryType;
    detail: string | null;
    note: string | null;
    at: string | null;
    until: string | null;
    photos: PortalPhoto[];
}

export interface PortalReport {
    id: number;
    mood: string | null;
    summary: string | null;
    published: boolean;
    entries: PortalReportEntry[];
}

export interface PortalReportChild {
    id: number;
    name: string;
    photo: string | null;
    report: PortalReport | null;
}

export interface PortalThread {
    id: number;
    guardian: string;
    lastMessageAt: string | null;
    unread: boolean;
}

export interface PortalMessage {
    id: number;
    body: string;
    author: string;
    mine: boolean;
    at: string | null;
    photos: PortalPhoto[];
}
