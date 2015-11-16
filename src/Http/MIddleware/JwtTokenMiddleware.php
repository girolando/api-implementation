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
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtTokenMiddleware
{
    protected $clientApp;
    protected $guard;
    protected $header;
    protected $payload;
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
     * @param  //\Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $resp = null;
        try {
            if ($request->headers->has('language'))
                App::setLocale($request->headers->get('language'));
            if($request->has('__plain')){
                try{
                    return $next($request);
                }catch(\Exception $e){
                    die(print_r(['status' => 'error', 'message' => $e->getMessage(), 'stack' => $e->getTraceAsString()]));
                }
            }

            //die(print_r($request->all()));
            if (!$request->has('__token')) throw new InvalidTokenException('The __token parameter doesn\'t has come inside the request.');

            //if (!preg_match('/[A-z0-9]{10,}\.[A-z0-9]{1,}\.[A-z0-9]{10,}/i', $request->get('__token'))) throw new InvalidTokenException('The received token does not appears like a valid JWT token.');

            $header = json_decode(base64_decode(explode('.', $request->get('__token'))[0]));
            //$payload = json_decode(base64_decode(explode('.', $request->get('__token'))[1]));
            $payload = $request->all();

            $this->header = $header;
            $this->payload = $payload;

            //The token wasn't adultered after being sent from client?
            //if (!isset($header->AppKey) || !$header->AppKey) throw new InvalidTokenException('No valid AppKey received.');
            if(isset($header->AppKey))
                $this->clientApp = $this->clientApp->newQuery()->where('usuarioAppCliente', '=', $header->AppKey)->first();

            if(!$this->clientApp && isset($payload->AppKey)) $this->clientApp = $this->clientApp->newQuery()->where('usuarioAppCliente', '=', $payload->AppKey)->first();

            if (!$this->clientApp) throw new InvalidTokenException('Could not find the client application this token works with.');

            \JWT::decode($request->get('__token'), $this->clientApp->secretAppCliente, [$header->alg]);

            //se chegou aki é pq ta supimpa, injeta a request e loga o manolo:
            $request->merge(json_decode(json_encode($payload), true));

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
            $resp = ['status' => 'error', 'data' => ['stack' => $e->getTraceAsString(), 'exception' => get_class($e)], 'message' => $e->getMessage()];
        }
        if($resp instanceof ApiResponse) return $resp;

        if($resp instanceof JsonResponse){
            $resp = [
                'status' => ($resp->getStatusCode() == 200) ? 'success' : 'error',
                'data' => $this->handleValidationErrors($resp->getData()),
                'message' => ($resp->getStatusCode() == 200) ? '' : 'You\'ve got some errors from request validator!'
            ];
        }
        return $this->renderResponse($resp);
        //\JWT::encode(payload, key, alg, keyid, head);
    }



    protected function renderResponse($resp)
    {
        //$resp = ['payload' => $resp];
        if($resp instanceof Response) $resp = ['status' => 'success', 'data' => $resp->getOriginalContent(), 'message' => ''];
        //if(!($resp instanceof ApiResponse)) $resp = ['success' => true, 'payload' => $resp];

        $user = null;
        $senha = null;
        if(!$this->clientApp || !$this->clientApp->exists) $this->clientApp = $this->clientApp->newQuery()->where('usuarioAppCliente','=',$this->payload->AppKey)->first();
        if(!$this->clientApp || !$this->clientApp->exists)
        {
            $user = (isset($this->header->AppKey)) ? $this->header->AppKey : 'NONE';
            $senha = 'NONE';
            $resp = ['status' => 'error', 'data' => ['exception' => 'ApiAuthException'], 'message' => 'Invalid client app signature.'];
        }
        //se não chegou header:
        if(!$this->header) $this->header = (object) ['AppKey' => 'NONE', 'alg' => 'HS256'];
        $jwt = \JWT::encode($resp, ($senha) ? $senha : $this->clientApp->secretAppCliente, $this->header->alg, null, ['AppKey' => ($user) ? $user : $this->clientApp->usuarioAppCliente]);
        return new ApiResponse($jwt, ($resp['status'] == 'success') ? 200 : 422);
    }

    public function handleValidationErrors($data = array()){
        if(get_class($data) == 'stdClass') return $data;
        $newData = array();
        foreach ($data as $field) {
            foreach ($field as $error) {
                $newData[] = $error;
            }
        }
        return $newData;

    }
}