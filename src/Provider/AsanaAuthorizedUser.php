<?php

namespace TestMonitor\Asana\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AsanaAuthorizedUser implements ResourceOwnerInterface
{
    protected $response;

    /**
     * AsanaAuthorizedUser constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->response['user_id'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->response['url'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getTeam()
    {
        return $this->response['team'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->response['user'] ?: null;
    }

    /**
     * @return int|null
     */
    public function getTeamId()
    {
        return $this->response['team_id'] ?: null;
    }

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->response['user_id'] ?: null;
    }
}
