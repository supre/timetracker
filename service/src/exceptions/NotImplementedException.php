<?php

namespace RoarProj\exceptions;

class NotImplementedException extends BaseException
{

    const METHOD_NOT_IMPLEMENTED = 'METHOD_NOT_IMPLEMENTED';

    static public function MethodNotImplemented($methodName)
    {
        return new NotImplementedException(
            self::METHOD_NOT_IMPLEMENTED,
            "Method $methodName is not implemented - cannot be called."
        );
    }


}