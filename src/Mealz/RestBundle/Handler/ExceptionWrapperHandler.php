<?php

namespace Mealz\RestBundle\Handler;

use FOS\RestBundle\Util\ExceptionWrapper;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;

class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface {

    public function wrap($data)
    {
        /** @var \Symfony\Component\Debug\Exception\FlattenException $exception */
        $exception = $data['exception'];

        $newException = array(
            'error' => 'invalid_grant',
            'exception' => $data['message'],
        );

        return $newException;
    }
}