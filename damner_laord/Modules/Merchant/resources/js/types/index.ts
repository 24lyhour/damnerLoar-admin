import type { Paginated } from '@/components/DamnerLaorTable';
import type { EnumOption } from '@/types';

export type MerchantTypeValue = 'bus_operator' | 'hotel';

export type MerchantStatusValue =
    | 'pending'
    | 'changes_requested'
    | 'active'
    | 'suspended'
    | 'rejected';

export type MerchantDocumentStatusValue =
    | 'missing'
    | 'pending_review'
    | 'verified'
    | 'expired'
    | 'rejected';

/** MerchantListResource::abilities(): same on the list and the detail page */
export type MerchantAbilities = {
    update: boolean;
    review: boolean;
    approve: boolean;
    requestChanges: boolean;
    reject: boolean;
    suspend: boolean;
    reactivate: boolean;
};

/** MerchantListResource */
export type MerchantListItem = {
    uuid: string;
    name: string;
    initials: string;
    subtitle: string | null;
    type: EnumOption<MerchantTypeValue>;
    city: string | null;
    commissionPercent: number;
    status: EnumOption<MerchantStatusValue>;
    joinedAt: string | null;
    legalName: string | null;
    contactName: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    address: string | null;
    province: string | null;
    can: MerchantAbilities;
};

/** MerchantDocumentResource (uuid is null for a required document never uploaded) */
export type MerchantChecklistItem = {
    uuid: string | null;
    type: EnumOption;
    status: EnumOption<MerchantDocumentStatusValue>;
    fileName: string | null;
    fileSize: number | null;
    note: string | null;
    expiresAt: string | null;
    verifiedAt: string | null;
    canVerify: boolean;
};

/** MerchantNoteResource */
export type MerchantNote = {
    uuid: string;
    body: string;
    author: string;
    createdAt: string | null;
};

/** MerchantDetailResource: a list item plus the approval-page extras */
export type MerchantDetail = MerchantListItem & {
    appliedAt: string | null;
    approvedAt: string | null;
    approvedBy: string | null;
    suspendedReason: string | null;
    pendingDocumentCount: number;
    checklist: MerchantChecklistItem[];
    notes: MerchantNote[];
};

/** Status changes with a confirm modal (route names confirm-<review>) */
export type MerchantReview =
    | 'approve'
    | 'request-changes'
    | 'reject'
    | 'suspend'
    | 'reactivate';

/** Props of merchant::Dashboard/V1/Merchant/Invite (GetMerchantCreateDataAction) */
export type MerchantCreateProps = {
    types: EnumOption<MerchantTypeValue>[];
    defaultCommission: Partial<Record<MerchantTypeValue, number>>;
};

/** Props of …/Merchant/Edit (GetMerchantEditDataAction) */
export type MerchantModalProps = {
    merchant: MerchantListItem;
    pendingDocumentCount: number;
};

/** Props of …/Merchant/Review (MerchantReviewController@confirm) */
export type MerchantReviewProps = MerchantModalProps & {
    review: MerchantReview;
};

/** Props of merchant::Dashboard/Merchant/Index */
export type MerchantIndexProps = {
    merchants: Paginated<MerchantListItem>;
    /** IndexMerchantRequest::filters(): every key present, null when unused */
    filters: {
        search: string | null;
        status: MerchantStatusValue | null;
        type: MerchantTypeValue | null;
        city: string | null;
        sort: string | null;
        per_page: number;
    };
    counts: Record<'all' | MerchantStatusValue, number>;
    cities: string[];
    types: EnumOption<MerchantTypeValue>[];
};
