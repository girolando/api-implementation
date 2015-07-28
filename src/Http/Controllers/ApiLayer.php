<?php

namespace Andersonef\ApiImplementation\Http\Controllers;

use Andersonef\ApiImplementation\Exceptions\ApiAuthException;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ApiLayer extends Controller
{

    public function postAuth(Request $request, Guard $guard, Repository $repository)
    {

        $user = app($repository->get('auth.model'))
            ->where('loginPessoa','=', $request->get('loginPessoa'))
            ->where('senhaPessoa','=', md5($request->get('senhaPessoa')))->first();

        if($user) return ['UserKey' => md5($user->codigoPessoa)];
        if($guard->attempt($request->all())) return ['UserKey' => strtoupper(md5($guard->user()->codigoPessoa))];

        throw new ApiAuthException('Invalid user or password!');
    }

    public function getDetails(Guard $guard)
    {
        return $guard->user();
    }
}
