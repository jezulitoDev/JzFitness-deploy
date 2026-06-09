import { Form, Head } from '@inertiajs/react';
import { Activity, AlertCircle, Link2, Unlink } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { connect, disconnect, index, sync } from '@/routes/strava';
import type { StravaAccount, StravaActivity } from '@/types/fitness';

/** OAuth must use a full page navigation; Inertia Link uses XHR and breaks the redirect. */
function StravaConnectAnchor({
    className,
    children,
}: {
    className?: string;
    children: ReactNode;
}) {
    const href = connect().url;

    return (
        <a href={href} className={className}>
            {children}
        </a>
    );
}

function formatDistance(meters: number | string): string {
    const km = Number(meters) / 1000;

    return `${km.toFixed(2)} km`;
}

function formatDuration(seconds: number): string {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);

    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function formatTokenExpiry(minutes: number, isoDate: string | null): string {
    if (minutes <= 0 && isoDate) {
        const date = new Date(isoDate);

        return `Expiró el ${date.toLocaleString('es-ES')}`;
    }

    if (minutes < 60) {
        return `Token activo · renueva en ${minutes} min`;
    }

    const hours = Math.floor(minutes / 60);

    return `Token activo · renueva en ${hours} h`;
}

export default function StravaIndex({
    stravaConfigured,
    account,
    tokenExpiresAt,
    tokenExpiresInMinutes,
    needsReconnect,
    activities,
}: {
    stravaConfigured: boolean;
    account: StravaAccount | null;
    tokenExpiresAt: string | null;
    tokenExpiresInMinutes: number | null;
    needsReconnect: boolean;
    activities: StravaActivity[];
}) {
    return (
        <>
            <Head title="Strava" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Strava</h1>
                        <p className="text-muted-foreground">
                            Sincroniza tus actividades automáticamente
                        </p>
                        {account && tokenExpiresAt && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                {formatTokenExpiry(
                                    tokenExpiresInMinutes ?? 0,
                                    tokenExpiresAt,
                                )}
                            </p>
                        )}
                    </div>
                    {account ? (
                        <div className="flex gap-2">
                            <Form {...sync.form.post()}>
                                <Button type="submit" variant="outline">
                                    Sincronizar
                                </Button>
                            </Form>
                            <Form {...disconnect.form.delete()}>
                                <Button type="submit" variant="destructive">
                                    <Unlink className="mr-2 size-4" />
                                    Desconectar
                                </Button>
                            </Form>
                        </div>
                    ) : stravaConfigured ? (
                        <Button asChild>
                            <StravaConnectAnchor>
                                <Link2 className="mr-2 size-4" />
                                Conectar Strava
                            </StravaConnectAnchor>
                        </Button>
                    ) : null}
                </div>

                {needsReconnect && (
                    <div className="flex items-start gap-3 rounded-xl border border-destructive/50 bg-destructive/10 p-4 text-sm">
                        <AlertCircle className="mt-0.5 size-5 shrink-0 text-destructive" />
                        <div>
                            <p className="font-medium">Reconexión necesaria</p>
                            <p className="text-muted-foreground">
                                El token de Strava ya no es válido. Vuelve a
                                conectar tu cuenta para seguir sincronizando.
                            </p>
                            <Button asChild className="mt-3" size="sm">
                                <StravaConnectAnchor>Conectar Strava</StravaConnectAnchor>
                            </Button>
                        </div>
                    </div>
                )}

                {!stravaConfigured && !account && !needsReconnect && (
                    <div className="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                        <p>
                            Configura{' '}
                            <code className="text-sm">STRAVA_CLIENT_ID</code> y{' '}
                            <code className="text-sm">STRAVA_CLIENT_SECRET</code>{' '}
                            en tu <code className="text-sm">.env</code>.
                        </p>
                        <p className="mt-2">
                            Crea una app en{' '}
                            <a
                                href="https://developers.strava.com"
                                target="_blank"
                                rel="noreferrer"
                                className="text-foreground underline"
                            >
                                developers.strava.com
                            </a>{' '}
                            y ejecuta{' '}
                            <code className="text-sm">php artisan config:clear</code>.
                        </p>
                    </div>
                )}

                {stravaConfigured && !account && !needsReconnect && (
                    <div className="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                        <p>Conecta tu cuenta de Strava para importar actividades.</p>
                        <p className="mt-2 text-sm">
                            Usa la misma URL que en <code>APP_URL</code> (p. ej.{' '}
                            <code>http://localhost:8000</code>), no mezcles con{' '}
                            <code>127.0.0.1</code>.
                        </p>
                        <Button asChild className="mt-4">
                            <StravaConnectAnchor>
                                <Link2 className="mr-2 size-4" />
                                Conectar Strava
                            </StravaConnectAnchor>
                        </Button>
                    </div>
                )}

                {activities.length > 0 && (
                    <div className="space-y-2">
                        <h2 className="font-medium">Últimas actividades</h2>
                        {activities.map((activity) => (
                            <div
                                key={activity.id}
                                className="flex items-center justify-between rounded-xl border p-4"
                            >
                                <div className="flex items-center gap-3">
                                    <Activity className="size-5 text-orange-500" />
                                    <div>
                                        <p className="font-medium">{activity.name}</p>
                                        <p className="text-sm text-muted-foreground">
                                            {activity.sport_type} ·{' '}
                                            {activity.started_at_label}
                                        </p>
                                    </div>
                                </div>
                                <div className="text-right text-sm text-muted-foreground">
                                    <p>{formatDistance(activity.distance)}</p>
                                    <p>{formatDuration(activity.moving_time)}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

StravaIndex.layout = {
    breadcrumbs: [{ title: 'Strava', href: index() }],
};
