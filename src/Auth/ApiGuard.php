<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 10/07/2015
 * Time: 14:45
 */

namespace Andersonef\ApiImplementation\Auth;


use Andersonef\ApiImplementation\Entities\Api\AppCliente;
use Illuminate\Auth\Guard;

class ApiGuard extends Guard {

    protected $clientApp;

    public function clientApp()
    {
        return $this->clientApp;
    }

    public function setClientApp(AppCliente $appCliente){
        $this->clientApp = $appCliente;
    }

    public function user()
    {
        $user = parent::user();
        if(!$user) return $this->clientApp();
    }


}