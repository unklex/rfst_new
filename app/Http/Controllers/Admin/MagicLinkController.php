<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminMagicLoginLink;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MagicLinkController extends Controller
{
    private const CACHE_PREFIX = 'magic-login:';

    public function showRequestForm(): View
    {
        return view('admin.magic-link.request');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $allowed = (string) config('auth.magic_link.email');
        $userEmail = (string) config('auth.magic_link.user_email');
        $sent = redirect()->route('admin.magic-link.sent');

        // Misconfigured environment — fail closed, but don't leak that fact.
        if ($allowed === '' || $userEmail === '') {
            Log::warning('Magic-link request received but ADMIN_MAGIC_LINK_EMAIL / ADMIN_EMAIL is not set.');

            return $sent;
        }

        // Constant-time compare on lower-cased addresses.
        $matches = hash_equals(
            mb_strtolower($allowed),
            mb_strtolower((string) $data['email']),
        );

        if (! $matches) {
            return $sent;
        }

        $user = User::where('email', $userEmail)->first();
        if ($user === null) {
            Log::warning('Magic-link allowlist matched but no User row found.', ['user_email' => $userEmail]);

            return $sent;
        }

        $token = Str::random(64);
        $ttl = max(1, (int) config('auth.magic_link.ttl_minutes', 15));

        Cache::put(self::CACHE_PREFIX . hash('sha256', $token), $user->id, now()->addMinutes($ttl));

        Mail::to($allowed)->send(new AdminMagicLoginLink(
            url: route('admin.magic-link.consume', ['token' => $token]),
            ttlMinutes: $ttl,
            ipAddress: (string) $request->ip(),
        ));

        return $sent;
    }

    public function showSent(): View
    {
        return view('admin.magic-link.sent');
    }

    public function consume(Request $request, string $token): RedirectResponse
    {
        $key = self::CACHE_PREFIX . hash('sha256', $token);
        $userId = Cache::pull($key);

        abort_if(! $userId, 403, 'Ссылка недействительна или уже использована.');

        $user = User::find($userId);
        abort_if(! $user, 403);

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        $panelUrl = Filament::getPanel('admin')->getUrl() ?? '/admin';

        return redirect()->intended($panelUrl);
    }
}
