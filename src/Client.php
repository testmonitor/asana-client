<?php

namespace TestMonitor\Asana;

use Exception;
use Asana\Client as AsanaClient;
use Asana\Dispatcher\OAuthDispatcher;
use Asana\Dispatcher\AccessTokenDispatcher;
use TestMonitor\Asana\Exceptions\InvalidTokenException;
use TestMonitor\Asana\Exceptions\TokenExpiredException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

class Client
{
    use Actions\ManagesAttachments,
        Actions\ManagesTasks,
        Actions\ManagesProjects,
        Actions\ManagesWorkspaces;

    /**
     * @var \TestMonitor\Asana\AccessToken
     */
    protected $token;

    /**
     * @var array
     */
    public $options = [
        'headers' => [
            'asana-enable' => 'string_ids,new_user_task_lists,new_project_templates,' .
                              'new_memberships,new_goal_memberships',
            'asana-disable' => 'new_sections',
        ],
    ];

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
     * @param \TestMonitor\Asana\AccessToken $token
     * @param array $options
     * @param OAuthDispatcher|null $dispatcher
     */
    public function __construct(
        array $credentials,
        AccessToken $token = null,
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
     * @return \TestMonitor\Asana\AccessToken
     */
    public function fetchToken(string $code): AccessToken
    {
        $accessToken = $this->dispatcher->fetchToken($code);

        $this->token = new AccessToken(
            $accessToken,
            $this->dispatcher->refreshToken,
            $this->dispatcher->expiresIn + time()
        );

        return $this->token;
    }

    /**
     * Refresh the current access token.
     *
     *@throws \Exception
     *
     * @return \TestMonitor\Asana\AccessToken
     */
    public function refreshToken(): AccessToken
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        try {
            $accessToken = $this->dispatcher->refreshAccessToken();
        } catch (Exception $e) {
            throw new InvalidTokenException($e->getMessage());
        }

        $this->token = new AccessToken(
            $accessToken,
            $this->dispatcher->refreshToken,
            $this->dispatcher->expiresIn + time()
        );

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
     * @throws \TestMonitor\Asana\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \Asana\Client
     */
    protected function client()
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

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
