<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToOauthProvider()
    {
        return Socialite::driver('azure-oauth')->redirect();
    }

    public function handleOauthResponse()
    {
        $user = Socialite::driver('azure-oauth')->user();

        $authUser = $this->findOrCreateUser($user);

       // Auth::guard('operator')->login($authUser);

        auth()->guard(config('azure-oath.guard'))->login($authUser);
        // session([
        //     'azure_user' => $user
        // ]);

        return redirect(
            config('azure-oath.redirect_on_login')
        );
    }

    protected function findOrCreateUser($user)
    {
        $user_class = config('azure-oath.user_class');
        $id_field = config('azure-oath.user_id_field');
        $authUser = $user_class::where('email', $user->email)->first();
        if ($authUser) {
            $authUser->$id_field = $user->id;
            $authUser->save();
            return $authUser;
        }

        $UserFactory = new UserFactory();

        return $UserFactory->convertAzureUser($user);
    }
}
