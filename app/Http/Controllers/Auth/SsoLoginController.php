<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SsoLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $plainToken = (string) $request->query('token', '');

        if ($plainToken === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid or expired sign-in link.',
            ]);
        }

        $connection = config('saas.connection', 'saas');
        $hashed = hash('sha256', $plainToken);

        $record = DB::connection($connection)
            ->table('login_tokens')
            ->where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid or expired sign-in link.',
            ]);
        }

        $user = User::query()
            ->where('email', $record->email)
            ->where('statut', 1)
            ->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Account not found. Please contact support.',
            ]);
        }

        DB::connection($connection)->table('login_tokens')->where('id', $record->id)->delete();

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()->away(rtrim(config('app.url'), '/').'/dashboard');
    }
}
