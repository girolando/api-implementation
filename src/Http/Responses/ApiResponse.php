<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 10/07/2015
 * Time: 10:18
 */

namespace Andersonef\ApiImplementation\Http\Responses;


use Illuminate\Http\Response;

class ApiResponse extends Response{

    public function setContent($content)
    {
        parent::setContent($content);
        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Jsonable ||
        $content instanceof ArrayObject ||
        is_array($content);
    }
}