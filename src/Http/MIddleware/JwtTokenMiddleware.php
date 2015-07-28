<?php

namespace Andersonef\ApiImplementation\Http\Middleware;

use Andersonef\ApiImplementation\Entities\Api\AppCliente;
use Andersonef\ApiImplementation\Exceptions\ApiAuthException;
use Andersonef\ApiImplementation\Exceptions\InvalidTokenException;
use Andersonef\ApiImplementation\Http\Responses\ApiResponse;
use Andersonef\ApiImplementation\Services\ApiAuthService;
use \Closure;
use Illuminate\Auth\Guard;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtTokenMiddleware
{
    protected $clientApp;
    protected $guard;
    protected $header;
    protected $apiService;
    protected $configRepository;

    public function __construct(AppCliente $appCliente, Guard $guard, ApiAuthService $service, Repository $repository)
    {
        $this->clientApp = $appCliente;
        $this->guard = $guard;
        $this->apiService = $service;
        $this->configRepository = $repository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $resp = null;
        try {
            //die(print_r($request->all()));
            if (!$request->has('__token')) throw new InvalidTokenException('The __token parameter doesn\'t has come inside the request.');
            if (!preg_match('/[A-z0-9]{10,}\.[A-z0-9]{1,}\.[A-z0-9]{10,}/i', $request->get('__token'))) throw new InvalidTokenException('The received token does not appears like a valid JWT token.');

            $header = json_decode(base64_decode(explode('.', $request->get('__token'))[0]));

            $this->header = $header;

            $payload = json_decode(base64_decode(explode('.', $request->get('__token'))[1]));

            //The token wasn't adultered after being sent from client?
            if (!isset($header->AppKey) || !$header->AppKey) throw new InvalidTokenException('No valid AppKey received.');

            $this->clientApp = $this->clientApp->newQuery()->where('usuarioAppCliente', '=', $header->AppKey)->first();

            if (!$this->clientApp) throw new InvalidTokenException('Could not find the client application this token works with.');

            \JWT::decode($request->get('__token'), $this->clientApp->secretAppCliente, [$header->alg]);

            //se chegou aki é pq ta supimpa, injeta a request e loga o manolo:
            $request->merge((array) $payload);

            //tem UserKey na header?? se tiver, loga o manolo:
            if(isset($this->header->UserKey))
            {

                $user = app($this->configRepository->get('auth.model'));

                $user = $user->newQuery()->whereRaw('HASHBYTES(\'md5\', cast('.$user->getKeyName().' as varchar)) = 0x'.$this->header->UserKey)->first();
                if(!$user) throw new ApiAuthException('Invalid UserKey: '.$this->header->UserKey);
                $this->guard->onceUsingId($user->getKey());
            }

            //logando o manolo:
            //if(isset($this->header->))

            $resp = $next($request);

        }catch (\Exception $e){
            $resp = ['status' => 'failure', 'data' => ['stack' => $e->getTraceAsString()], 'message' => $e->getMessage()];
        }
        if($resp instanceof ApiResponse) return $resp;
        if($resp instanceof JsonResponse) $resp = $this->handleValidationErrors($resp->getData());
        return $this->renderResponse($resp);
        //\JWT::encode(payload, key, alg, keyid, head);
    }



    protected function renderResponse($resp)
    {
        $response = new ApiResponse();
        //$resp = ['payload' => $resp];
        if($resp instanceof Response) $resp = ['status' => 'success', 'data' => $resp->getOriginalContent(), 'message' => ''];
        //if(!($resp instanceof ApiResponse)) $resp = ['success' => true, 'payload' => $resp];

        $user = null;
        $senha = null;
        if(!$this->clientApp || !$this->clientApp->exists)
        {
            $user = (isset($this->header->AppKey)) ? $this->header->AppKey : 'NONE';
            $senha = 'NONE';
            $resp = ['status' => 'failure', 'data' => ['exception' => 'Invalid client app signature.']];
        }
        //se não chegou header:
        if(!$this->header) $this->header = (object) ['AppKey' => 'NONE', 'alg' => 'HS256'];
        $jwt = \JWT::encode($resp, ($senha) ? $senha : $this->clientApp->secretAppCliente, $this->header->alg, null, ['AppKey' => ($user) ? $user : $this->clientApp->usuarioAppCliente]);
        return $response->setContent($jwt);
    }

    public function handleValidationErrors($data = array()){
        $newData = array();
        foreach ($data as $field) {
            foreach ($field as $error) {
                $newData[] = $error;
            }
        }
        return $newData;

    }
}
