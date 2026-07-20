/** Shared shapes for the daycare portal. Children are records, never accounts. */

export interface PortalClass {
    id: number;
    name: string;
    label: string;
    grade: string | null;
    year: string;
    color: string | null;
    banner: string | null;
    teachers: { id: number; name: string }[];
    childCount: number;
}

export interface PortalGuardian {
    id: number;
    name: string;
    relationship: string | null;
}

export interface PortalReportCard {
    id: number;
    title: string;
    issuedOn: string | null;
    note: string | null;
    published: boolean;
    file: { url: string; name: string; size: number } | null;
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
    reportCards: PortalReportCard[];
}

export interface PortalComment {
    id: number;
    author: string;
    body: string;
    at: string | null;
    mine: boolean;
}

export interface PortalEvent {
    title: string;
    month: string;
    day: string;
    dateLabel: string;
    timeLabel: string;
    location: string | null;
}

export interface PortalPost {
    id: number;
    body: string;
    author: string;
    createdAt: string | null;
    photos: string[];
    type: 'update' | 'event';
    event: PortalEvent | null;
    likesCount: number;
    likedByMe: boolean;
    comments: PortalComment[];
}

export type ReportEntryType =
    'nap' | 'meal' | 'nappy' | 'mood' | 'note' | 'photo';

export interface PortalReportEntry {
    id: number;
    type: ReportEntryType;
    label: string | null;
    detail: string | null;
    note: string | null;
    at: string | null;
    until: string | null;
    photos: string[];
}

export interface PortalReport {
    id: number;
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

export interface PortalFamily {
    guardianId: number;
    name: string;
    conversationId: number | null;
    lastMessageAt: string | null;
    isAnnouncement: boolean;
}

export interface PortalMessage {
    id: number;
    body: string;
    author: string;
    mine: boolean;
    at: string | null;
    photos: string[];
}

/** Pickers for the day sheet, driven by App\Models\ReportEntry so the form
 *  can only offer what the controller accepts. */
export interface ReportOptions {
    details: Record<string, string[]>;
    labels: Record<string, string[]>;
}

/** A class a student can be enrolled into, from the directory picker. */
export interface DirectoryClass {
    id: number;
    name: string;
    grade: string | null;
    year: string;
}

/** One row of a student's class history (null endedOn = current room). */
export interface EnrollmentRow {
    id: number;
    classroom: string | null;
    year: string | null;
    startedOn: string | null;
    endedOn: string | null;
    current: boolean;
}

/** A family member linked to a student. */
export interface DirectoryGuardian {
    id: number;
    name: string;
    email: string;
    relationship: string | null;
}

/** A report card shown on a student's detail. */
export interface DirectoryReportCard {
    id: number;
    title: string;
    issuedOn: string | null;
    published: boolean;
    file: { url: string; name: string };
}

/** A student in the global directory — persists across classes and years. */
export interface DirectoryStudent {
    id: number;
    name: string;
    firstName: string;
    lastName: string;
    dob: string | null;
    age: number | null;
    photo: string | null;
    notes: string | null;
    inviteCode: string | null;
    guardianCount: number;
    guardians: DirectoryGuardian[];
    reportCards: DirectoryReportCard[];
    currentClass: { id: number; name: string; year: string } | null;
    history: EnrollmentRow[];
}
