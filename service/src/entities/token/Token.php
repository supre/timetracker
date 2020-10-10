<?php

namespace RoarProj\entities\token;

use Lcobucci\JWT\Token as JWTToken;

class Token
{
    const CLAIM_ROLE = "role";
    const CLAIM_EMAIL = "email";
    const CLAIM_TEAM = "team";
    const CLAIM_DISPLAY_NAME = "displayName";
    const CLAIM_REFRESH_TOKEN = "refreshToken";
    const CLAIM_PREFERRED_WORKING_HOURS = 'preferredWorkingHours';

    public function __construct(JWTToken $jwtToken, JWTToken $refreshToken = null)
    {
        $this->accessToken = $jwtToken;
        $this->refreshToken = $refreshToken;
    }

    public function getRole()
    : string
    {
        return $this->accessToken->getClaim(self::CLAIM_ROLE);
    }

    public function getEmail()
    : string
    {
        return $this->accessToken->getClaim(self::CLAIM_EMAIL);
    }

    public function getTeam()
    : string
    {
        return $this->accessToken->getClaim(self::CLAIM_TEAM);
    }

    public function getDisplayName()
    : string
    {
        return $this->accessToken->getClaim(self::CLAIM_DISPLAY_NAME);
    }

    public function getAccessToken()
    : string
    {
        return (string)$this->accessToken;
    }

    public function getRefreshToken()
    : string
    {
        return $this->refreshToken ? (string)$this->refreshToken : '';
    }

    private $accessToken;
    /**
     * @var JWTToken|null
     */
    private $refreshToken;
}