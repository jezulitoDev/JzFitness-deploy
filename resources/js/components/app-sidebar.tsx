import { Link } from '@inertiajs/react';
import {
    Activity,
    Apple,
    Calendar,
    Dumbbell,
    LayoutGrid,
    ListChecks,
    Play,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as calendar } from '@/routes/calendar';
import { index as exercises } from '@/routes/exercises';
import { index as gymSessions } from '@/routes/gym-sessions';
import { index as nutrition } from '@/routes/nutrition';
import { index as strava } from '@/routes/strava';
import { index as workoutPlans } from '@/routes/workout-plans';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Calendario', href: calendar(), icon: Calendar },
    { title: 'Ejercicios', href: exercises(), icon: Dumbbell },
    { title: 'Rutinas', href: workoutPlans(), icon: ListChecks },
    { title: 'Entrenamientos', href: gymSessions(), icon: Play },
    { title: 'Nutrición', href: nutrition(), icon: Apple },
    { title: 'Strava', href: strava(), icon: Activity },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
