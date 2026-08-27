<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Modules\Auth\Services\AuthService;
use Throwable;

class AuthController extends Controller
{
    public function loginPage(): Response
    {
        $request = Request::capture();
        $returnTo = $this->safeReturnPath((string) $request->query('return_to', ''))
            ?? $this->safeReturnPath((string) Session::get('_auth.intended', ''));

        if ($returnTo !== null) {
            Session::put('_auth.intended', $returnTo);
        }

        return $this->view('Auth::login', [
            'title' => 'Connexion | AfiaZone',
            'errors' => Session::consumeFlash('auth.errors', []),
            'old' => Session::consumeFlash('auth.old', []),
            'returnTo' => $returnTo,
        ]);
    }

    public function login(): Response
    {
        $request = Request::capture();
        $data = [
            'identifier' => trim((string) $request->input('identifier', '')),
            'password' => (string) $request->input('password', ''),
        ];
        $validator = new Validator($data);

        if (! $validator->validate([
            'identifier' => 'required|string|max:190',
            'password' => 'required|string',
        ])) {
            return $this->backWithErrors($validator->errors(), ['identifier' => $data['identifier']]);
        }

        if (! (new AuthService())->attempt($data['identifier'], $data['password'], $request->ip())) {
            return $this->backWithErrors(['identifier' => ['Identifiants invalides ou acces temporairement limite.']], ['identifier' => $data['identifier']]);
        }

        $returnTo = $this->safeReturnPath((string) $request->input('return_to', ''))
            ?? $this->safeReturnPath((string) Session::get('_auth.intended', ''));
        Session::forget('_auth.intended');

        if ($returnTo !== null) {
            return $this->redirect($returnTo);
        }

        return $this->redirect(Auth::hasRole('merchant') ? '/marchand' : (Auth::hasAnyRole(['admin', 'super_admin']) ? '/admin' : '/compte'));
    }

    public function logout(): Response
    {
        Auth::logout();
        return $this->redirect(url());
    }

    public function verificationPage(): Response
    {
        return $this->view('Auth::verify-email', [
            'title' => 'Verification email | AfiaZone',
            'email' => Session::consumeFlash('auth.verification_email'),
        ]);
    }

    public function verifyEmail(string $token): Response
    {
        if (! (new AuthService())->verifyEmail($token)) {
            Session::flash('auth.message', 'Ce lien de verification est invalide ou expire.');
            return $this->redirect('/connexion');
        }

        Session::flash('auth.message', 'Adresse email verifiee. Vous pouvez maintenant vous connecter.');
        return $this->redirect('/connexion');
    }

    public function resendVerificationEmail(): Response
    {
        $request = Request::capture();
        $email = strtolower(trim((string) $request->input('email', '')));
        $validator = new Validator(['email' => $email]);
        if (! $validator->validate(['email' => 'required|email|max:190'])) {
            Session::flash('auth.message', 'Indiquez une adresse email valide.');
            return $this->redirect('/verification-email');
        }

        $service = new AuthService();
        $service->resendVerificationEmail($email);
        Session::flash('auth.verification_email', $email);
        Session::flash('auth.message', 'Si ce compte est en attente, un nouveau lien a ete prepare.');
        return $this->redirect('/verification-email');
    }

    public function registerPage(): Response
    {
        return $this->registerView(false);
    }

    public function merchantRegisterPage(): Response
    {
        return $this->registerView(true);
    }

    public function registerCustomer(): Response
    {
        return $this->register(false);
    }

    public function registerMerchant(): Response
    {
        return $this->register(true);
    }

    public function forgotPage(): Response
    {
        return $this->view('Auth::forgot-password', [
            'title' => 'Mot de passe oublie | AfiaZone',
            'errors' => Session::consumeFlash('auth.errors', []),
            'old' => Session::consumeFlash('auth.old', []),
        ]);
    }

    public function forgotPassword(): Response
    {
        $request = Request::capture();
        $identifier = trim((string) $request->input('identifier', ''));
        $validator = new Validator(['identifier' => $identifier]);

        if (! $validator->validate(['identifier' => 'required|string|max:190'])) {
            return $this->backWithErrors($validator->errors(), ['identifier' => $identifier], '/mot-de-passe-oublie');
        }

        $token = (new AuthService())->requestPasswordReset($identifier);
        Session::flash('auth.message', 'Si un compte correspond, les instructions de reinitialisation seront transmises.');

        return $this->redirect('/mot-de-passe-oublie');
    }

    public function resetPage(string $token): Response
    {
        return $this->view('Auth::reset-password', [
            'title' => 'Nouveau mot de passe | AfiaZone',
            'token' => $token,
            'errors' => Session::consumeFlash('auth.errors', []),
        ]);
    }

    public function resetPassword(string $token): Response
    {
        $request = Request::capture();
        $data = [
            'password' => (string) $request->input('password', ''),
            'password_confirmation' => (string) $request->input('password_confirmation', ''),
        ];
        $validator = new Validator($data);

        if (! $validator->validate(['password' => 'required|string|min:8|max:255'])) {
            return $this->backWithErrors($validator->errors(), [], '/reset-password/' . rawurlencode($token));
        }
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->backWithErrors(['password_confirmation' => ['Les mots de passe ne correspondent pas.']], [], '/reset-password/' . rawurlencode($token));
        }

        if (! (new AuthService())->resetPassword($token, $data['password'])) {
            return $this->backWithErrors(['password' => ['Ce lien est invalide ou expire.']], [], '/reset-password/' . rawurlencode($token));
        }

        Session::flash('auth.message', 'Votre mot de passe a ete modifie.');
        return $this->redirect('/connexion');
    }

    private function registerView(bool $merchant): Response
    {
        return $this->view('Auth::register', [
            'title' => $merchant ? 'Inscription marchand | AfiaZone' : 'Inscription | AfiaZone',
            'merchant' => $merchant,
            'errors' => Session::consumeFlash('auth.errors', []),
            'old' => Session::consumeFlash('auth.old', []),
        ]);
    }

    private function register(bool $merchant): Response
    {
        $request = Request::capture();
        $data = [
            'full_name' => trim((string) $request->input('full_name', '')),
            'business_name' => trim((string) $request->input('business_name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => trim((string) $request->input('phone', '')),
            'password' => (string) $request->input('password', ''),
            'password_confirmation' => (string) $request->input('password_confirmation', ''),
        ];
        $rules = [
            'full_name' => 'required|string|min:2|max:160',
            'email' => 'required|email|max:190',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|max:255',
        ];
        if ($merchant) {
            $rules['business_name'] = 'required|string|min:2|max:190';
        }
        $validator = new Validator($data);

        if (! $validator->validate($rules)) {
            return $this->backWithErrors($validator->errors(), $this->oldRegistration($data), $merchant ? '/inscription/marchand' : '/inscription');
        }
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->backWithErrors(['password_confirmation' => ['Les mots de passe ne correspondent pas.']], $this->oldRegistration($data), $merchant ? '/inscription/marchand' : '/inscription');
        }

        try {
            $service = new AuthService();
            $userId = $service->register($data, $merchant ? 'merchant' : 'customer');
            $service->sendVerificationEmail($userId);
            Session::flash('auth.verification_email', $data['email']);
        } catch (Throwable) {
            return $this->backWithErrors(['email' => ['Cette adresse email ou ce numero est deja utilise.']], $this->oldRegistration($data), $merchant ? '/inscription/marchand' : '/inscription');
        }

        return $this->redirect('/verification-email');
    }

    private function oldRegistration(array $data): array
    {
        unset($data['password'], $data['password_confirmation']);
        return $data;
    }

    private function backWithErrors(array $errors, array $old = [], string $path = '/connexion'): Response
    {
        Session::flash('auth.errors', $errors);
        Session::flash('auth.old', $old);
        return $this->redirect($path);
    }

    private function safeReturnPath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path;
    }
}