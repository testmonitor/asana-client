<?php

namespace TestMonitor\Asana;

class AccessToken
{
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var string
     */
    public $expiresIn;

    /**
     * Token constructor.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expiresIn
     */
    public function __construct(string $accessToken = '', string $refreshToken = '', int $expiresIn = 0)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }

    /**
     * Determines if the access token has expired.
     *
     * @return bool
     */
    public function expired()
    {
        return ($this->expiresIn - time()) < 60;
    }

    /**
     * Returns the token as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
        ];
    }
}
