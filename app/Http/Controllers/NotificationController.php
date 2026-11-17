<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Services\NotificationFeedService;
use App\Services\NotificationLinkResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman riwayat pemberitahuan pengguna.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $unreadNotificationCount = $request->user()->unreadNotifications()->count();

        $view = $request->user()->role === Role::Admin
            ? 'admin.notifications.index'
            : 'kepala-dinas.notifications.index';

        return view($view, [
            'notifications' => $notifications,
            'unreadNotificationCount' => $unreadNotificationCount,
        ]);
    }

    /**
     * Sediakan data terbaru untuk pembaruan topbar tanpa memuat ulang halaman.
     */
    public function feed(Request $request, NotificationFeedService $feed): JsonResponse
    {
        $summary = $feed->summaryFor($request->user());

        return response()
            ->json([
                'unread_count' => $summary['unread_count'],
                'content_html' => view('layouts.partials.notification-menu-content', [
                    'notifications' => $summary['notifications'],
                    'unreadNotificationCount' => $summary['unread_count'],
                ])->render(),
            ])
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Tandai seluruh notifikasi pengguna sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Tandai satu pemberitahuan sebagai dibaca dan arahkan ke menu terkait.
     */
    public function read(Request $request, string $notification, NotificationLinkResolver $links): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect($links->resolve($notification, $request->user()) ?? route('notifications.index'));
    }

    /**
     * Hapus satu pemberitahuan milik pengguna.
     */
    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->delete();

        return back()->with('success', 'Pemberitahuan berhasil dihapus.');
    }
}
