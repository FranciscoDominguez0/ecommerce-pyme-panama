<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Log to debug roles
        \Illuminate\Support\Facades\Log::info('Login attempt', [
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'hasRole_cliente' => $user->hasRole('cliente'),
            'isEmpty' => $user->roles->isEmpty(),
        ]);

        $isCustomer = $user->hasRole('cliente') || $user->roles->isEmpty();
        $isAdmin = !$isCustomer;

        if ($isAdmin) {
            $request->session()->flash('is_from_login', true);
            $intended = session()->pull('url.intended');
            
            if ($intended && str_contains($intended, '/admin')) {
                $url = $intended;
            } else {
                $url = route('admin.dashboard', absolute: false);
            }
        } else {
            $request->session()->flash('is_from_login', true);
            $url = session()->pull('url.intended', route('dashboard', absolute: false));
        }

        \Illuminate\Support\Facades\Log::info('Login redirect decision', [
            'isAdmin' => $isAdmin,
            'url' => $url,
            'wantsJson' => $request->wantsJson(),
            'ajax' => $request->ajax()
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => $url,
                'isAdmin' => $isAdmin
            ]);
        }

        return redirect()->to($url);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
