<?php


namespace RoarProj\exceptions;


class AuthorizationException extends BaseException
{

    public static function fromKeyNotFound($keyId)
    {
        throw new static("No key found for token");
    }

    public static function fromExpiredToken()
    {
        throw new static("Token has expired", "Token has expired");
    }

    public static function fromTamperedToken()
    {
        throw new static("Token is tampered");
    }

    public static function fromInvalidToken(\Exception $e)
    {
        throw new static("Invalid token");
    }
}