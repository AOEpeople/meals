<?php

namespace App\Mealz\RestBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'error' => 'invalid_grant',
            // TODO: [Upgrade] What's with $data['message']
//            'exception' => $data['message'],
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        // TODO: [Upgrade] Do we need a new exception class ?
//        return $data instanceof \My\Exception;
        return false;
    }
}
