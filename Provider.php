<?php

namespace SocialiteProviders\Instructure;

use GuzzleHttp\RequestOptions;
use RuntimeException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'INSTRUCTURE';

    protected $scopes = ['url:GET|/api/v1/users/:user_id/profile'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getInstanceUrl().'/login/oauth2/auth', $state);
    }

    public static function additionalConfigKeys(): array
    {
        return ['instance_url'];
    }

    protected function getTokenUrl(): string
    {
        return $this->getInstanceUrl().'/login/oauth2/token?replace_tokens=1';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getInstanceUrl().'/api/v1/users/self/profile', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
        ]);
    }

    /**
     * Get the instance URI.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function getInstanceUrl()
    {
        $instanceUrl = $this->getConfig('instance_url');

        if ($instanceUrl === null) {
            throw new RuntimeException('The instance_url configuration key is not set.');
        }

        return $instanceUrl;
    }
}
