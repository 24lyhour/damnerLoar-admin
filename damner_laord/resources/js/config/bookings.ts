import { Building2, Bus, Package } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import type { BookingStatus, ServiceType, StatusTone } from '@/types';

export const bookingStatusMeta: Record<
    BookingStatus,
    { label: string; tone: StatusTone }
> = {
    confirmed: { label: 'Confirmed', tone: 'success' },
    awaiting_payment: { label: 'Awaiting payment', tone: 'neutral' },
    payment_failed: { label: 'Payment failed', tone: 'error' },
    cancelled: { label: 'Cancelled', tone: 'error' },
    boarding: { label: 'Boarding', tone: 'brand' },
};

export const serviceMeta: Record<
    ServiceType,
    { label: string; icon: LucideIcon }
> = {
    bus: { label: 'Bus', icon: Bus },
    hotel: { label: 'Hotel', icon: Building2 },
    package: { label: 'Package', icon: Package },
};
