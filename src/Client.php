<?php

namespace TestMonitor\Asana;

use Asana\Client as AsanaClient;
use Asana\Dispatcher\Dispatcher;
use Asana\Dispatcher\OAuthDispatcher;
use Asana\Dispatcher\AccessTokenDispatcher;
use TestMonitor\Asana\Exceptions\TokenExpiredException;

class Client
{
    use Actions\ManagesAttachments,
        Actions\ManagesTasks,
        Actions\ManagesProjects,
        Actions\ManagesWorkspaces;

    /**
     * @var \TestMonitor\Asana\Token
     */
    protected $token;

    /**
     * @var array
     */
    public $options = ['headers' => ['asana-disable' => 'new_sections']];

    /**
     * @var \Asana\Client
     */
    protected $client;

    /**
     * @var \Asana\Dispatcher\OAuthDispatcher
     */
    protected $dispatcher;

    /**
     * Create a new client instance.
     *
     * @param array $credentials
     * @param \TestMonitor\Asana\Token $token
     * @param array $options
     * @param OAuthDispatcher|null $dispatcher
     */
    public function __construct(
        array $credentials,
        Token $token = null,
        array $options = [],
        OAuthDispatcher $dispatcher = null
    ) {
        $this->token = $token;

        $this->dispatcher = $dispatcher ?? new OAuthDispatcher([
            'client_id' => $credentials['clientId'],
            'client_secret' => $credentials['clientSecret'],
            'redirect_uri' => $credentials['redirectUrl'],
            'refresh_token' => $token->refreshToken ?? null,
        ]);

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Create a new authorization URL for the given state.
     *
     * @param string $state
     * @return string
     */
    public function authorizationUrl($state)
    {
        return $this->dispatcher->authorizationUrl($state);
    }

    /**
     * Fetch the access and refresh token based on the authorization code.
     *
     * @param string $code
     * @return \TestMonitor\Asana\Token
     */
    public function fetchToken(string $code): Token
    {
        $accessToken = $this->dispatcher->fetchToken($code);

        $this->token = new Token($accessToken, $this->dispatcher->refreshToken, $this->dispatcher->expiresIn + time());

        return $this->token;
    }

    /**
     * Refresh the current access token.
     *
     * @throws \Exception
     * @return \TestMonitor\Asana\Token
     */
    public function refreshToken(): Token
    {
        $accessToken = $this->dispatcher->refreshAccessToken();

        $this->token = new Token($accessToken, $this->dispatcher->refreshToken, $this->dispatcher->expiresIn + time());

        return $this->token;
    }

    /**
     * Determines if the current access token has expired.
     *
     * @return bool
     */
    public function tokenExpired()
    {
        return $this->token->expired();
    }

    /**
     * Returns an Asana client instance.
     *
     * @throws TokenExpiredException
     * @return \Asana\Client
     */
    protected function client()
    {
        if ($this->token->expired()) {
            throw new TokenExpiredException();
        }

        return $this->client ?? new AsanaClient(new AccessTokenDispatcher($this->token->accessToken), $this->options);
    }

    /**
     * @param \Asana\Client $client
     */
    public function setClient(AsanaClient $client)
    {
        $this->client = $client;
    }
}
