<?php

namespace TestMonitor\Asana\Provider;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class AsanaProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->assertRequiredOptions($options);

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions(): array
    {
        return [
            'clientId',
            'clientSecret',
            'redirectUri',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);

        if (! empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.asana.com/-/oauth_authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://app.asana.com/-/oauth_token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param $token
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl($token): string
    {
        $params = [
            'token' => $token->getToken(),
            'user'  => $this->getAuthorizedUser($token)->getId(),
        ];

        return 'https://app.asana.com/api/1.0/users/me?' . http_build_query($params);
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        //
    }

    /**
     * Create new resource owner using the generated access token.
     *
     * @param array $response
     * @param AccessToken $token
     * @return AsanaAuthorizedUser
     */
    protected function createResourceOwner(array $response, $token)
    {
        return new AsanaAuthorizedUser($response);
    }

    /**
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * @param AccessToken $token
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return mixed
     */
    public function fetchAuthorizedUserDetails(AccessToken $token): mixed
    {
        $url = $this->getAuthorizedUserTestUrl();

        $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

        // Keep compatibility with League\OAuth2\Client v1
        if (! method_exists($this, 'getParsedResponse')) {
            return $this->getResponse($request);
        }

        return $this->getParsedResponse($request);
    }

    /**
     * @param AccessToken $token
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     *
     * @return AsanaAuthorizedUser
     */
    public function getAuthorizedUser($token): AsanaAuthorizedUser
    {
        $response = $this->fetchAuthorizedUserDetails($token);

        return $this->createAuthorizedUser($response);
    }

    /**
     * @param $response
     * @return AsanaAuthorizedUser
     */
    protected function createAuthorizedUser($response): AsanaAuthorizedUser
    {
        return new AsanaAuthorizedUser($response);
    }
}
