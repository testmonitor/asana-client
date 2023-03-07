<?php

namespace TestMonitor\Asana;

use Psr\Http\Message\ResponseInterface;
use TestMonitor\Asana\Exceptions\Exception;
use TestMonitor\Asana\Provider\AsanaProvider;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\ValidationException;
use TestMonitor\Asana\Exceptions\FailedActionException;
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
     * @var string
     */
    protected $enable = 'string_ids,new_user_task_lists,new_project_templates';

    /**
     * @var string
     */
    protected $disable = 'new_sections';

    /**
     * @var string
     */
    protected $baseUrl = 'https://app.asana.com/api';

    /**
     * @var string
     */
    protected $apiVersion = '1.0';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \TestMonitor\Asana\Provider\AsanaProvider
     */
    protected $provider;

    /**
     * Create a new client instance.
     *
     * @param array $credentials
     * @param \TestMonitor\Asana\AccessToken $token
     * @param OAuthDispatcher|null $dispatcher
     * @param string|null $enable
     * @param string|null $disable
     */
    public function __construct(
        array $credentials,
        AccessToken $token = null,
        AsanaProvider $provider = null,
        string $enable = null,
        string $disable = null
    ) {
        $this->token = $token;

        $this->provider = $provider ?? new AsanaProvider([
            'clientId' => $credentials['clientId'],
            'clientSecret' => $credentials['clientSecret'],
            'redirectUri' => $credentials['redirectUrl'],
            'refresh_token' => $token->refreshToken ?? null,
        ]);

        $this->enable = $enable ?? $this->enable;
        $this->disable = $disable ?? $this->disable;
    }

    /**
     * Create a new authorization URL for the given state.
     *
     * @param $state
     * @return string
     */
    public function authorizationUrl($state = [])
    {
        return $this->provider->getAuthorizationUrl($state);
    }

    /**
     * Fetch the access and refresh token based on the authorization code.
     *
     * @param string $code
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return \TestMonitor\Asana\AccessToken
     */
    public function fetchToken(string $code)
    {
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $this->token = AccessToken::fromAsana($token);

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

        $token = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->token->refreshToken,
        ]);

        $this->token = AccessToken::fromAsana($token);

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
     * Returns an Guzzle client instance.
     *
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     * @throws TokenExpiredException
     *
     * @return \GuzzleHttp\Client
     */
    protected function client()
    {
        if (empty($this->token)) {
            throw new UnauthorizedException();
        }

        if ($this->token->expired()) {
            throw new TokenExpiredException();
        }

        return $this->client ?? new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl . '/' . $this->apiVersion . '/',
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'asana-enable' => $this->enable,
                'asana-disable' => $this->disable,
            ],
        ]);
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * Make a GET request to Asana servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Asana\Exceptions\FailedActionException
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Asana\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function get($uri, array $payload = [])
    {
        return $this->request('GET', $uri, $payload);
    }

    /**
     * Make a POST request to Asana servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Asana\Exceptions\FailedActionException
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Asana\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function post($uri, array $payload = [])
    {
        return $this->request('POST', $uri, ['form_params' => $payload]);
    }

    /**
     * Make a PUT request to Forge servers and return the response.
     *
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Asana\Exceptions\FailedActionException
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Asana\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function patch($uri, array $payload = [])
    {
        return $this->request('PATCH', $uri, $payload);
    }

    /**
     * Make request to Asana servers and return the response.
     *
     * @param string $verb
     * @param string $uri
     * @param array $payload
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \TestMonitor\Asana\Exceptions\FailedActionException
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\TokenExpiredException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     * @throws \TestMonitor\Asana\Exceptions\ValidationException
     *
     * @return mixed
     */
    protected function request($verb, $uri, array $payload = [])
    {
        $response = $this->client()->request(
            $verb,
            $uri,
            $payload,
        );

        if (! in_array($response->getStatusCode(), [200, 201, 203, 204, 206])) {
            return $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody();

        return json_decode($responseBody, true) ?: $responseBody;
    }

    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \TestMonitor\Asana\Exceptions\ValidationException
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\FailedActionException
     * @throws \Exception
     *
     * @return void
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException();
        }

        if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
            throw new UnauthorizedException();
        }

        if ($response->getStatusCode() == 400) {
            throw new FailedActionException((string) $response->getBody());
        }

        throw new Exception((string) $response->getStatusCode());
    }
}
