<?php

namespace Andersonef\ApiImplementation\Entities\Api;

use Illuminate\Database\Eloquent\Model;

class TokenAcesso extends Model
{
    protected $table = 'api.TokenAcesso';
    public static $snakeAttributes = false;
}
