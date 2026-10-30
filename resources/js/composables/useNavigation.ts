import { usePage } from '@inertiajs/vue3';
import {
    Building2,
    CalendarClock,
    Landmark,
    LayoutGrid,
    MapPinned,
    Phone,
    Plane,
    ShieldCheck,
    Smartphone,
    UserCog,
    UserRound,
    Users,
    UsersRound,
    UtensilsCrossed,
} from '@lucide/vue';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import AgencyController from '@/actions/App/Http/Controllers/Admin/AgencyController';
import ContactController from '@/actions/App/Http/Controllers/Admin/ContactController';
import DeviceController from '@/actions/App/Http/Controllers/Admin/DeviceController';
import FlightGroupController from '@/actions/App/Http/Controllers/Admin/FlightGroupController';
import GroupController from '@/actions/App/Http/Controllers/Admin/GroupController';
import LocationController from '@/actions/App/Http/Controllers/Admin/LocationController';
import MealController from '@/actions/App/Http/Controllers/Admin/MealController';
import PilgrimController from '@/actions/App/Http/Controllers/Admin/PilgrimController';
import PlaceController from '@/actions/App/Http/Controllers/Admin/PlaceController';
import ScheduleController from '@/actions/App/Http/Controllers/Admin/ScheduleController';
import StaffController from '@/actions/App/Http/Controllers/Admin/StaffController';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

export type NavSection = {
    title: string;
    items: NavItem[];
};

/**
 * The top menu and its side menus, filtered by role. The top menu links to
 * a section's first item; the side menu lists the rest of that section.
 */
export function useNavigation(): {
    sections: ComputedRef<NavSection[]>;
    current: ComputedRef<NavSection | undefined>;
} {
    const page = usePage();
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const role = computed(() => page.props.auth.user.role);

    const sections = computed<NavSection[]>(() => {
        const isSystemAdmin = role.value === 'system_admin';
        const isAgencyAdmin = role.value === 'agency_admin';

        const pilgrims: NavItem[] = isSystemAdmin
            ? []
            : [
                  {
                      title: 'Pilgrims',
                      href: PilgrimController.index(),
                      icon: Users,
                  },
                  ...(isAgencyAdmin
                      ? [
                            {
                                title: 'Devices',
                                href: DeviceController.index(),
                                icon: Smartphone,
                            },
                        ]
                      : []),
                  {
                      title: 'Pilgrim locations',
                      href: LocationController.index(),
                      icon: MapPinned,
                  },
                  {
                      title: 'Groups',
                      href: GroupController.index(),
                      icon: UsersRound,
                  },
                  ...(isAgencyAdmin
                      ? [
                            {
                                title: 'Trips',
                                href: FlightGroupController.index(),
                                icon: Plane,
                            },
                        ]
                      : []),
              ];

        const schedule: NavItem[] = isAgencyAdmin
            ? [
                  {
                      title: 'Schedules',
                      href: ScheduleController.index(),
                      icon: CalendarClock,
                  },
                  {
                      title: 'Meals',
                      href: MealController.index(),
                      icon: UtensilsCrossed,
                  },
              ]
            : [];

        const settings: NavItem[] = [
            { title: 'Profile', href: editProfile(), icon: UserRound },
            { title: 'Security', href: editSecurity(), icon: ShieldCheck },
        ];

        if (role.value !== 'group_leader') {
            settings.push({
                title: 'Staff accounts',
                href: StaffController.index(),
                icon: UserCog,
            });
        }

        if (isSystemAdmin) {
            settings.push({
                title: 'Agencies',
                href: AgencyController.index(),
                icon: Building2,
            });
        }

        return [
            {
                title: 'Home',
                items: [
                    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
                ],
            },
            { title: 'Pilgrims', items: pilgrims },
            { title: 'Schedule', items: schedule },
            {
                title: 'Holy sites',
                items: isSystemAdmin
                    ? [
                          {
                              title: 'Holy sites',
                              href: PlaceController.index(),
                              icon: Landmark,
                          },
                      ]
                    : [],
            },
            {
                title: 'Contacts',
                items: isAgencyAdmin
                    ? [
                          {
                              title: 'Contacts',
                              href: ContactController.index(),
                              icon: Phone,
                          },
                      ]
                    : [],
            },
            { title: 'Settings', items: settings },
        ].filter((section) => section.items.length > 0);
    });

    const current = computed(() =>
        sections.value.find((section) =>
            section.items.some((item) => isCurrentOrParentUrl(item.href)),
        ),
    );

    return { sections, current };
}
