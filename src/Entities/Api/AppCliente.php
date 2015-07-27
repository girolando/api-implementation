<?php

namespace Andersonef\ApiImplementation\Entities\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class AppCliente extends Model implements AuthenticatableContract, CanResetPasswordContract{
    use Authenticatable, CanResetPassword;

    protected $table = 'api.AppCliente';
    public static $snakeAttributes = false;



    public function Servicos()
    {
        return $this->belongsToMany('Andersonef\Entities\Api\Servico', 'agd.ServicoAppCliente', 'idAppCliente', 'idServico');
    }

}
