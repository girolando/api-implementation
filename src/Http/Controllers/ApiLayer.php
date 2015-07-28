<?php

namespace Andersonef\ApiImplementation\Http\Controllers;

use Andersonef\ApiImplementation\Exceptions\ApiAuthException;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;

use Inet\Http\Requests;
use Inet\Http\Controllers\Controller;

class ApiLayer extends Controller
{

    public function postAuth(Request $request, Guard $guard)
    {
        $user = app($this->configRepository->get('auth.model'))
            ->where('loginPessoa','=', $request->get('loginPessoa'))
            ->where('senhaPessoa','=', md5($request->get('senhaPessoa')))->first();

        if($user) return ['UserKey' => md5($user->codigoPessoa)];
        if($guard->attempt($request->all())) return ['UserKey' => md5($guard->user()->codigoPessoa)];

        throw new ApiAuthException('Invalid user or password!');

    }
}
