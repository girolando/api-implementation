<?php

namespace Andersonef\ApiImplementation\Entities\Api;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $table = 'api.Servicos';
    public static $snakeAttributes = false;
}
