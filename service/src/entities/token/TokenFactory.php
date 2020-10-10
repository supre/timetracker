<?php


namespace RoarProj\entities\token;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token as JWTToken;
use Lcobucci\JWT\ValidationData;
use RoarProj\entities\user\User;
use RoarProj\exceptions\AuthorizationException;

/**
 * Class TokenFactory
 * @package RoarProj\entities\token
 */
class TokenFactory
{
    public function __construct(int $tokenDuration)
    {
        $this->privateKey = "This is a dummy string for roar tracker";
        $this->tokenDuration = $tokenDuration;
    }

    /**
     * @param User $user
     * @return Token
     */
    public function createUserToken(User $user): Token
    {
        return $this->createAccessToken(
            time() + $this->tokenDuration,
            $user->getId(),
            $user->getRole(),
            $user->getEmail(),
            $user->getTeam(),
            $user->getDisplayName(),
            $user->getPreferredWorkingHours()
        );
    }

    /**
     * @param $jwtTokenString
     * @return Token
     * @throws AuthorizationException
     */
    public function fromString($jwtTokenString): Token
    {
        $token = $this->parseToken((string)$jwtTokenString);
        $this->validateToken($token);

        return new Token($token);
    }

    private function createAccessToken(
        int $expiration,
        string $subject,
        string $role,
        string $email,
        string $team,
        string $displayName,
        ?float $preferredWorkingHours
    ): Token {
        $jwtToken = (new Builder())
            ->expiresAt($expiration)
            ->relatedTo($subject)
            ->withClaim(Token::CLAIM_ROLE, $role)
            ->withClaim(Token::CLAIM_EMAIL, $email)
            ->withClaim(Token::CLAIM_DISPLAY_NAME, $displayName)
            ->withClaim(Token::CLAIM_TEAM, $team)
            ->withClaim(Token::CLAIM_PREFERRED_WORKING_HOURS, $preferredWorkingHours)
            ->getToken($this->getSigner(), $this->getKey());

        return new Token($jwtToken, $this->createRefreshToken($subject));
    }

    private function createRefreshToken(string $subject): JWTToken
    {
        $thirtyDaysInSeconds = 30 * 86400;

        return (new Builder())
            ->expiresAt($thirtyDaysInSeconds)
            ->relatedTo($subject)
            ->withClaim(TOKEN::CLAIM_REFRESH_TOKEN, true)
            ->getToken($this->getSigner(), $this->getKey());
    }

    private function getKey(): Key
    {
        return new Key($this->privateKey);
    }

    private function validateToken(JWTToken $token): bool
    {
        $data = new ValidationData();

        if (!$token->validate($data)) {
            throw AuthorizationException::fromExpiredToken();
        }


        if (!$token->verify($this->getSigner(), $this->getKey())) {
            throw AuthorizationException::fromTamperedToken();
        }

        return true;
    }

    /**
     * @param $tokenString
     * @return JWTToken
     * @throws AuthorizationException
     */
    private function parseToken($tokenString): JWTToken
    {
        $parser = new Parser();
        try {
            $parsedToken = $parser->parse($tokenString);
        } catch (\Exception $e) {
            throw AuthorizationException::fromInvalidToken($e);
        }

        return $parsedToken;
    }

    private function getSigner(): Signer
    {
        return new Signer\Hmac\Sha256();
    }

    /**
     * @var integer
     */
    private $tokenDuration;
    /**
     * @var string
     */
    private $privateKey;
}
