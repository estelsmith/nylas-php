<?php

namespace Nylas\Utilities;

use Nylas\Request\Sync;
use Nylas\Request\Async;
use Nylas\Accounts\Account;
use Nylas\Utilities\Validator as V;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Utils Options
 * ----------------------------------------------------------------------------------
 *
 * @author lanlin
 * @change 2021/03/18
 */
class Options
{
    // ------------------------------------------------------------------------------

    /**
     * @var mixed
     */
    private $logFile;

    /**
     * @var bool
     */
    private bool $debug = false;

    /**
     * @var string
     */
    private string $server;

    private string $apiKey;

    /**
     * The user's Grant ID.
     *
     * @see https://developer.nylas.com/docs/v3/auth/manage-grants/
     */
    private ?string $grantId = null;

    /**
     * TODO Remove in favor of {@see $grantId}.
     *
     * @var string
     */
    private string $accessToken;

    /**
     * @var string
     */
    private string $accountId;

    /**
     * @var array
     */
    private array $accountInfo;

    // ------------------------------------------------------------------------------

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $rules = V::keySet(
            V::key('debug', V::boolType(), false),
            V::key('region', V::in(['us', 'eu']), false),
            V::key('log_file', $this->getLogFileRule(), false),
            V::key('account_id', V::stringType()->notEmpty(), false),
            V::key('grant_id', V::stringType()->notEmpty(), false),
            V::key('access_token', V::stringType()->notEmpty(), false),
            V::key('api_key', V::stringType()->notEmpty()),
        );

        V::doValidate($rules, $options);

        // required
        $this->setClientApps($options['api_key']);

        // optional
        $this->setDebug($options['debug'] ?? false);
        $this->setServer($options['region'] ?? 'us');
        $this->setLogFile($options['log_file'] ?? null);
        $this->setAccountId($options['account_id'] ?? '');
        $this->setGrantId($options['grant_id'] ?? null);
        $this->setAccessToken($options['access_token'] ?? '');
    }

    public function getGrantId(): ?string
    {
        return $this->grantId;
    }

    public function setGrantId(?string $grantId): void
    {
        $this->grantId = $grantId;
    }

    // ------------------------------------------------------------------------------

    /**
     * set access token
     *
     * @param string $token
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;

        if (!$token)
        {
            return;
        }

        $this->accountInfo = [];
    }

    // ------------------------------------------------------------------------------

    /**
     * get access token
     *
     * @return string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken ?? null;
    }

    // ------------------------------------------------------------------------------

    /**
     * set account id
     *
     * @param string $id
     */
    public function setAccountId(string $id): void
    {
        $this->accountId = $id;
    }

    // ------------------------------------------------------------------------------

    /**
     * get account id
     *
     * @return string
     */
    public function getAccountId(): ?string
    {
        return $this->accountId ?? null;
    }

    // ------------------------------------------------------------------------------

    /**
     * @param null|string $region
     */
    public function setServer(?string $region = null): void
    {
        $region = $region ?? 'us';

        $this->server = API::SERVER[$region] ?? API::SERVER['us'];
    }

    // ------------------------------------------------------------------------------

    /**
     * get server
     *
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    // ------------------------------------------------------------------------------

    /**
     * enable/disable debug
     *
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    // ------------------------------------------------------------------------------

    /**
     * set log file
     *
     * @param mixed $logFile
     */
    public function setLogFile($logFile): void
    {
        if (null !== $logFile)
        {
            V::doValidate($this->getLogFileRule(), $logFile);
        }

        $this->logFile = $logFile;
    }

    // ------------------------------------------------------------------------------

    /**
     * Set Nylas API token.
     */
    public function setClientApps(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    // ------------------------------------------------------------------------------

    /**
     * Retrieve Nylas API token.
     *
     * TODO Usages still rely on old client_id and client_secret.
     *
     * @return array
     */
    public function getClientApps(): array
    {
        return
        [
            'api_key' => $this->apiKey,
        ];
    }

    // ------------------------------------------------------------------------------

    /**
     * get all configure options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        return
        [
            'debug'         => $this->debug,
            'log_file'      => $this->logFile,
            'server'        => $this->server,
            'api_key'       => $this->apiKey,
            'account_id'    => $this->accountId,
            'grant_id'      => $this->grantId,
            'access_token'  => $this->accessToken,
        ];
    }

    // ------------------------------------------------------------------------------

    /**
     * get sync request instance
     *
     * @return \Nylas\Request\Sync
     */
    public function getSync(): Sync
    {
        $server = $this->getServer();

        $debug = $this->getLoggerHandler();

        return new Sync($server, $debug);
    }

    // ------------------------------------------------------------------------------

    /**
     * get async request instance
     *
     * @return \Nylas\Request\Async
     */
    public function getAsync(): Async
    {
        $server = $this->getServer();

        $debug = $this->getLoggerHandler();

        return new Async($server, $debug);
    }

    // ------------------------------------------------------------------------------

    /**
     * get account infos
     *
     * @return array
     */
    public function getAccount(): array
    {
        $temp =
        [
            'id'                => '',
            'account_id'        => '',
            'email_address'     => '',
            'name'              => '',
            'object'            => '',
            'provider'          => '',
            'linked_at'         => null,
            'sync_state'        => '',
            'organization_unit' => '',
        ];

        if (empty($this->accountInfo) && !empty($this->accessToken))
        {
            $this->accountInfo = (new Account($this))->getAccount();
        }

        return \array_merge($temp, $this->accountInfo);
    }

    // ------------------------------------------------------------------------------

    /**
     * get log file rules
     *
     * @return \Nylas\Utilities\Validator
     */
    private function getLogFileRule(): V
    {
        return V::oneOf(
            V::resourceType(),
            V::stringType()->notEmpty()
        );
    }

    // ------------------------------------------------------------------------------

    /**
     * get logger handler
     *
     * @return mixed
     */
    private function getLoggerHandler()
    {
        switch (true)
        {
            case \is_string($this->logFile):
            return \fopen($this->logFile, 'ab');

            case \is_resource($this->logFile):
            return $this->logFile;

            default:
            return $this->debug;
        }
    }

    // ------------------------------------------------------------------------------
}
