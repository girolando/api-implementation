<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 13/07/2015
 * Time: 08:53
 */

namespace Andersonef\ApiImplementation\Services;


use Andersonef\ApiImplementation\Entities\Api\AppCliente;
use Andersonef\ApiImplementation\Entities\Api\Servico;
use Illuminate\Auth\Guard;

class ApiAuthService {

    protected $clientApp;
    protected $loggedUser;
    protected $guard;
    protected $service;

    function __construct(Guard $guard, Servico $servico)
    {
        $this->guard =$guard;
        $this->service = $servico;
    }

    public function user()
    {
        return $this->guard->user();
    }

    public function clientApp()
    {
        return $this->clientApp;
    }

    public function setClientApp(AppCliente $appCliente)
    {
        $this->clientApp = $appCliente;
    }

    public function appCanUseService(AppCliente $appCliente, $route)
    {
        $service = $this->service->newQuery()->where('routeServico', '=', $route)->first();
        return $appCliente->Servicos->contains($service->id);
    }

}