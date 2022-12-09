<?php

namespace One23\PhalconPhp\Encryption;

use One23\PhalconPhp\Di\DiInterface;
use One23\PhalconPhp\Di\AbstractInjectionAware;
use One23\PhalconPhp\Http\RequestInterface;
use One23\PhalconPhp\Encryption\Security\Random;
use One23\PhalconPhp\Encryption\Security\Exception;
use One23\PhalconPhp\Session\ManagerInterface as SessionInterface;

class Security extends AbstractInjectionAware
{
    const CRYPT_ARGON2I    = 10;
    const CRYPT_ARGON2ID   = 11;
    const CRYPT_BCRYPT     = 0;
    const CRYPT_DEFAULT    = 0;
    const CRYPT_BLOWFISH   = 4;
    const CRYPT_BLOWFISH_A = 5;
    const CRYPT_BLOWFISH_X = 6;
    const CRYPT_BLOWFISH_Y = 7;
    const CRYPT_EXT_DES    = 2;
    const CRYPT_MD5        = 3;
    const CRYPT_SHA256     = 8;
    const CRYPT_SHA512     = 9;
    const CRYPT_STD_DES    = 1;

    protected int $defaultHash = self::CRYPT_DEFAULT;

    protected int $numberBytes = 16;

    protected Random $random;

    protected ?string $requestToken = null;

    protected ?string $token = null;

    protected ?string $tokenKey = null;

    protected string $tokenKeySessionId = "\$PHALCON/CSRF/KEY$";

    protected string $tokenValueSessionId = "\$PHALCON/CSRF$";

    protected int $workFactor = 10;

    private ?SessionInterface $localSession = null;

    private ?RequestInterface $localRequest = null;

    public function __construct(
        SessionInterface $session = null,
        RequestInterface $request = null
    ) {
        $this->random       = new Random();
        $this->localRequest = $request;
        $this->localSession = $session;
    }

    /**
     * Checks a plain text password and its hash version to check if the
     * password matches
     */
    public function checkHash(
        string $password,
        string $passwordHash,
        int $maxPassLength = 0
    ): bool {
        if ($maxPassLength > 0 && strlen($password) > $maxPassLength) {
            return false;
        }

        return password_verify($password, $passwordHash);
    }

    /**
     * Check if the CSRF token sent in the request is the same that the current
     * in session
     */
    public function checkToken(
        string $tokenKey = null,
        mixed $tokenValue = null,
        bool $destroyIfValid = true
    ): bool {
        $tokenKey = $this->processTokenKey($tokenKey);

        /**
         * If tokenKey does not exist in session return false
         */
        if (!$tokenKey) {
            return false;
        }

        /**
         * The value is the same?
         */
        $userToken  = $this->processUserToken($tokenKey, $tokenValue);
        $knownToken = $this->getRequestToken();

        if (is_null($knownToken)  || is_null($userToken)) {
            return false;
        }

        $equals = hash_equals($knownToken, $userToken);

        /**
         * Remove the key and value of the CSRF token in session
         */
        if ($equals && $destroyIfValid) {
            $this->destroyToken();
        }

        return $equals;
    }

    /**
     * Computes a HMAC
     *
     * @throws Exception
     */
    public function computeHmac(
        string $data,
        string $key,
        string $algo,
        bool $raw = false
    ): string {
        $hmac = hash_hmac($algo, $data, $key, $raw);

        if (!$hmac) {
            throw new Exception(
                sprintf(
                    "Unknown hashing algorithm: %s",
                    $algo
                )
            );
        }

        return $hmac;
    }

    /**
     * Removes the value of the CSRF token and key from session
     */
    public function destroyToken(): Security
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService("session", "localSession");

        if (!is_null($session)) {
            $session->remove($this->tokenKeySessionId);
            $session->remove($this->tokenValueSessionId);
        }

        $this->token        = null;
        $this->tokenKey     = null;
        $this->requestToken = null;

        return $this;
    }

    /**
     * Returns the default hash
     */
    public function getDefaultHash(): int
    {
        return $this->defaultHash;
    }

    /**
     * Returns information regarding a hash
     */
    public function getHashInformation(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * Returns a secure random number generator instance
     */
    public function getRandom(): Random
    {
        return $this->random;
    }

    /**
     * Returns a number of bytes to be generated by the openssl pseudo random
     * generator
     */
    public function getRandomBytes(): int
    {
        return $this->numberBytes;
    }

    /**
     * Returns the value of the CSRF token for the current request.
     */
    public function getRequestToken(): ?string
    {
        if (empty($this->requestToken)) {
            return $this->getSessionToken();
    }

        return $this->requestToken;
    }

    /**
     * Returns the value of the CSRF token in session
     */
    public function getSessionToken(): ?string
    {
        /** @var SessionInterface|null $session */
        $session = $this->getLocalService("session", "localSession");

        if (!is_null($session)) {
            return $session->get($this->tokenValueSessionId);
        }

        return null;
    }

    /**
     * Generate a >22-length pseudo random string to be used as salt for
     * passwords
     *
     * @throws Exception
     */
    public function getSaltBytes(int $numberBytes = 0): string
    {
        if ($numberBytes <= 0) {
            $numberBytes = $this->numberBytes;
        }

        while(true) {
            $safeBytes = $this->random->base64Safe($numberBytes);

            if ($safeBytes && strlen($safeBytes) >= $numberBytes) {
                break;
            }
        }

        return $safeBytes;
    }

    /**
     * Generates a pseudo random token value to be used as input's value in a
     * CSRF check
     *
     * @throws Exception
     */
    public function getToken(): ?string
    {
        if (is_null($this->token)) {
            $this->requestToken = $this->getSessionToken();
            $this->token        = $this->random->base64Safe($this->numberBytes);

            /** @var SessionInterface|null $session */
            $session = $this->getLocalService("session", "localSession");

            if (!is_null($session)) {
                $session->set(
                    $this->tokenValueSessionId,
                    $this->token
                );
            }
        }

        return $this->token;
    }

    /**
     * Generates a pseudo random token key to be used as input's name in a CSRF
     * check
     *
     * @throws Exception
     */
    public function getTokenKey(): string | null
    {
        if (is_null($this->token)) {
            /** @var SessionInterface|null $session */
            $session = $this->getLocalService("session", "localSession");

            if (!is_null($session)) {
                $this->tokenKey = $this->random->base64Safe($this->numberBytes);
                $session->set(
                    $this->tokenKeySessionId,
                    $this->tokenKey
                );
            }
        }

        return $this->tokenKey;
    }

    public function getWorkFactor(): int
    {
        return $this->workFactor;
    }

    /**
     * Creates a password hash using bcrypt with a pseudo random salt
     */
    public function hash(string $password, array $options = []): string
    {
        /**
         * The `legacy` variable distinguishes between `password_hash` and
         * non `password_hash` hashing.
         */
        $cost      = $this->processCost($options);
        $formatted = sprintf("%02s", $cost);
        $prefix    = "";
        $bytes     = 22;
        $legacy    = true;

        switch ($this->defaultHash) {
            case self::CRYPT_MD5:
                /*
                 * MD5 hashing with a twelve character salt
                 * SHA-256/SHA-512 hash with a sixteen character salt.
                 */
                $prefix = "$1$";
                $bytes  = 12;
                break;

            case self::CRYPT_SHA256:
                $prefix = "$5$";
                $bytes  = 16;
                break;

            case self::CRYPT_SHA512:
                $prefix = "$6$";
                $bytes  = 16;
                break;

            /*
             * Blowfish hashing with a salt as follows: "$2a$", "$2x$" or
             * "$2y$", a two digit cost parameter, "$", and 22 characters
             * from the alphabet "./0-9A-Za-z". Using characters outside
             * this range in the salt will cause `crypt()` to return a
             * zero-length string. The two digit cost parameter is the
             * base-2 logarithm of the iteration count for the underlying
             * Blowfish-based hashing algorithm and must be in range 04-31,
             * values outside this range will cause crypt() to fail.
             */
            case self::CRYPT_BLOWFISH_A:
                $prefix = sprintf("$2a$%s$", $formatted);
                break;

            case self::CRYPT_BLOWFISH_X:
                $prefix = sprintf("$2x$%s$", $formatted);
                break;

            default:
                $legacy = false;
                break;
        }

        if ($legacy) {
            $salt = $prefix . $this->getSaltBytes($bytes) . "$";

            return crypt($password, $salt);
        }

        /**
         * This is using password_hash
         *
         * We will not provide a "salt" but let PHP calculate it.
         */
        $options = [
            "cost" => $cost
        ];

        $algorithm = $this->processAlgorithm();
        $arguments = $this->processArgonOptions($options);

        return password_hash($password, $algorithm, $arguments);
    }

    /**
     * Checks if a password hash is a valid bcrypt's hash
     */
    public function isLegacyHash(string $passwordHash): bool
    {
        return str_starts_with($passwordHash, "$2a$");
    }

    /**
     * Sets the default hash
     */
    public function setDefaultHash(int $defaultHash): Security
    {
        $this->defaultHash = $defaultHash;

        return $this;
    }

    /**
     * Sets a number of bytes to be generated by the openssl pseudo random
     * generator
     */
    public function setRandomBytes(int $randomBytes): Security
    {
        $this->numberBytes = $randomBytes;

        return $this;
    }

    /**
     * Sets the work factor
     */
    public function setWorkFactor(int $workFactor): Security
    {
        $this->workFactor = $workFactor;

        return $this;
    }

    protected function getLocalService(string $name, string $property): RequestInterface|SessionInterface|null
    {
        if (
            is_null($this->{$property}) &&
            !is_null($this->container) &&
            true === $this->container->has($name)
        ) {
            $this->{$property} = $this->container->getShared($name);
        }

        return $this->{$property};
    }

    /**
     * Checks the algorithm for `password_hash`. If it is argon based, it
     * returns the relevant constant
     */
    private function processAlgorithm(): string
    {
        $algorithm = PASSWORD_BCRYPT;

        if ($this->defaultHash === self::CRYPT_ARGON2I) {
            $algorithm = PASSWORD_ARGON2I;
        }
        elseif ($this->defaultHash === self::CRYPT_ARGON2ID) {
            $algorithm = PASSWORD_ARGON2ID;
        }

        return $algorithm;
    }

    /**
     * We check if the algorithm is Argon based. If yes, options are set for
     * `password_hash` such as `memory_cost`, `time_cost` and `threads`
     */
    private function processArgonOptions(array $options): array
    {
        if (
            $this->defaultHash === self::CRYPT_ARGON2I ||
            $this->defaultHash === self::CRYPT_ARGON2ID
        ) {
            $options["memory_cost"] = $options["memory_cost"] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
            $options["time_cost"]   = $options["time_cost"] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST;
            $options["threads"]     = $options["threads"]   ?? PASSWORD_ARGON2_DEFAULT_THREADS;
        }

        return $options;
    }

    /**
     * Checks the options array for `cost`. If not defined it is set to 10.
     * It also checks the cost if it is between 4 and 31
     */
    private function processCost(array $options = []): int
    {
        $cost = $this->workFactor;
        if (!empty($options["cost"])) {
            $cost = (int)$options["cost"];
        }

        if ($cost < 4) {
            $cost = 4;
        }

        if ($cost > 31) {
            $cost = 31;
        }

        return $cost;
    }

    private function processTokenKey(string $tokenKey = null): ?string
    {
        $key     = $tokenKey;
        $session = $this->getLocalService("session", "localSession");

        if (!is_null($session) && empty($key)) {
            $key = $session->get($this->tokenKeySessionId);
        }

        return $key;
    }

    private function processUserToken(
        string $tokenKey,
        string $tokenValue = null
    ): ?string {
        $userToken = $tokenValue;

        if (!$tokenValue) {
            /** @var RequestInterface|null $request */
            $request = $this->getLocalService("request", "localRequest");

            /**
             * We always check if the value is correct in post
             */
            if (!is_null($request)) {
                /** @var string|null $userToken */
                $userToken = $request->getPost($tokenKey, "string");
            }
        }

        return $userToken;
    }
}
