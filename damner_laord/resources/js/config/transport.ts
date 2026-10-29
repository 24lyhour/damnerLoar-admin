import type { StatusTone, TripStatus } from '@/types';

export const tripStatusMeta: Record<
    TripStatus,
    { label: string; tone: StatusTone }
> = {
    scheduled: { label: 'Scheduled', tone: 'neutral' },
    boarding: { label: 'Boarding', tone: 'brand' },
    departed: { label: 'Departed', tone: 'success' },
    delayed: { label: 'Delayed', tone: 'error' },
    cancelled: { label: 'Cancelled', tone: 'error' },
};
