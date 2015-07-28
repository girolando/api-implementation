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
        if(!$guard->attempt($request->all())) throw new ApiAuthException('Invalid user or password!');
        return ['UserKey' => md5($guard->user()->codigoPessoa)];
    }
}
