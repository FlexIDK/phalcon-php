<?php

namespace One23\PhalconPhp\Encryption;

use One23\PhalconPhp\Encryption\Crypt\CryptInterface;
use One23\PhalconPhp\Encryption\Crypt\Exception\Exception;
use One23\PhalconPhp\Encryption\Crypt\Exception\Mismatch;
use One23\PhalconPhp\Encryption\Crypt\PadFactory;

class Crypt implements CryptInterface {

    /**
     * Defaults
     */
    const DEFAULT_ALGORITHM = "sha256";
    const DEFAULT_CIPHER    = "aes-256-cfb";

    /**
     * Padding
     */
    const PADDING_ANSI_X_923     = 1;
    const PADDING_DEFAULT        = 0;
    const PADDING_ISO_10126      = 3;
    const PADDING_ISO_IEC_7816_4 = 4;
    const PADDING_PKCS7          = 2;
    const PADDING_SPACE          = 6;
    const PADDING_ZERO           = 5;

    protected string $authData = "";
    protected string $authTag = "";
    protected int $authTagLength = 16;

    /**
     * Available cipher methods.
     */
    protected array $availableCiphers = [];

    protected string $cipher = self::DEFAULT_CIPHER;

    /**
     * The name of hashing algorithm.
     */
    protected string $hashAlgorithm = self::DEFAULT_ALGORITHM;

    /**
     * The cipher iv length.
     */
    protected int $ivLength = 16;

    protected string $key = "";

    protected int $padding = 0;

    protected PadFactory $padFactory;

    /**
     * Whether calculating message digest enabled or not.
     */
    protected bool $useSigning = true;

    /**
     * Crypt constructor.
     *
     * @throws Exception
     */
    public function __construct(
        string $cipher = self::DEFAULT_CIPHER,
        bool $useSigning = true,
        ?PadFactory $padFactory = null
    ) {
        if (is_null($padFactory))  {
            $padFactory = new PadFactory();
        }

        $this->padFactory    = $padFactory;
        $this->hashAlgorithm = self::DEFAULT_ALGORITHM;

        $this
            ->initializeAvailableCiphers()
            ->setCipher($cipher)
            ->useSigning($useSigning)
        ;
    }

    /**
     * Sets the cipher algorithm for data encryption and decryption.
     *
     * @throws Exception
     */
    public function setCipher(string $cipher): CryptInterface
    {
        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $this->ivLength = $this->getIvLength($cipher);
        $this->cipher   = $cipher;

        return $this;
    }

    /**
     * Sets if the calculating message digest must used.
     */
    public function useSigning(bool $useSigning): CryptInterface
    {
        $this->useSigning = $useSigning;

        return $this;
    }

    /**
     * @throws Exception
     */
    private function getIvLength(string $cipher): int
    {
        $length = openssl_cipher_iv_length($cipher);
        if (false === $length) {
            throw new Exception(
                "Cannot calculate the initialization vector (IV) length of the cipher"
            );
        }

        return $length;
    }

    /**
     * Checks if a cipher or a hash algorithm is available
     *
     * @throws Exception
     */
    protected function checkCipherHashIsAvailable(string $cipher, string $type): void
    {
        if ("hash" === $cipher) {
            $available = $this->getAvailableHashAlgorithms();
        } else {
            $available = $this->getAvailableCiphers();
        }

        $lower     = mb_strtolower(
            $cipher
        );

        if (!isset($available[$lower])) {
            throw new Exception(
                sprintf(
                    "The '%s' algorithm '%s' is not supported on this system.",
                    $type,
                    $cipher
                )
            );
        }
    }

    /**
     * Returns a list of available ciphers.
     */
    public function getAvailableCiphers(): array
    {
        return $this->availableCiphers;
    }

    /**
     * Return a list of registered hashing algorithms suitable for hash_hmac.
     */
    public function getAvailableHashAlgorithms(): array
    {
        if (function_exists('hash_hmac_algos')) {
            return hash_hmac_algos();
        }

        return hash_algos();
    }

    /**
     * Initialize available cipher algorithms.
     *
     * @throws Exception
     */
    protected function initializeAvailableCiphers(): Crypt
    {
        if (!function_exists('openssl_get_cipher_methods')) {
            throw new Exception("This class requires the openssl extension for PHP");
        }

        $available = openssl_get_cipher_methods(true);
        $allowed   = [];

        foreach ($available as $cipher) {
            if (
                true !== str_starts_with($cipher, "des") &&
                true !== str_starts_with($cipher, "rc2") &&
                true !== str_starts_with($cipher, "rc4") &&
                true !== str_ends_with($cipher, "ecb")
            ) {
                $allowed[$cipher] = $cipher;
            }
        }

        $this->availableCiphers = $allowed;

        return $this;
    }


    /**
     * @return int|false
     */
    protected function phpOpensslCipherIvLength(string $cipher)
    {
        return openssl_cipher_iv_length($cipher);
    }

    /**
     * @return string|false
     */
    protected function phpOpensslRandomPseudoBytes(int $length)
    {
        return openssl_random_pseudo_bytes($length);
    }

    /**
     * Decrypt a text that is coded as a base64 string.
     *
     * @throws Exception
     * @throws Mismatch
     */
    public function decryptBase64(
        string $input,
        ?string $key = null,
        bool $safe = false
    ): string {
        if ($safe) {
            $input = strtr($input, "-_", "+/") .
                mb_substr(
                    "===",
                    (mb_strlen($input) + 3) % 4
                );
        }

        $decode = base64_decode($input);
        if ($decode === false) {
            throw new Exception("Cannot decode base64 input value");
        }

        return $this->decrypt(
            $decode,
            $key
        );
    }

    /**
     * Encrypts a text returning the result as a base64 string.
     *
     * @throws Exception
     */
    public function encryptBase64(
        string $input,
        string $key = null,
        bool $safe = false
    ): string {
        if ($safe) {
            return rtrim(
                strtr(
                    base64_encode(
                        $this->encrypt($input, $key)
                    ),
                    "+/",
                    "-_"
                ),
                "="
            );
        }

        return base64_encode($this->encrypt($input, $key));
    }

    /**
     * Returns the current cipher
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * Returns the encryption key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the encryption key.
     *
     * The `$key` should have been previously generated in a cryptographically
     * safe way.
     */
    public function setKey(string $key): CryptInterface
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Changes the padding scheme used.
     */
    public function setPadding(int $scheme): CryptInterface
    {
        $this->padding = $scheme;

        return $this;
    }

    /**
     * Sets the authentication tag length
     */
    public function setAuthTagLength(int $length): CryptInterface
    {
        $this->authTagLength = $length;

        return $this;
    }

    /**
     * Sets authentication data
     */
    public function setAuthData(string $data): CryptInterface
    {
        $this->authData = $data;

        return $this;
    }

    /**
     * Sets the authentication tag
     */
    public function setAuthTag(string $tag): CryptInterface
    {
        $this->authTag = $tag;

        return $this;
    }

    /**
     * Returns the auth tag length
     */
    public function getAuthTagLength(): int
    {
        return $this->authTagLength;
    }

    /**
     * Returns the auth data
     */
    public function getAuthData(): string
    {
        return $this->authData;
    }

    /**
     * Returns the auth tag
     */
    public function getAuthTag(): string
    {
        return $this->authTag;
    }

    /**
     * Returns the mode (last few characters of the cipher)
     */
    private function getMode(): string
    {
        $position = intval(mb_strrpos(
            $this->cipher,
            "-"
        ));

        return mb_strtolower(
            mb_substr(
                $this->cipher,
                $position - mb_strlen(
                    $this->cipher
                ) + 1
            )
        );
    }

    /**
     * Returns the block size
     *
     * @throws Exception
     */
    private function getBlockSize(string $mode): int
    {
        if ($this->ivLength > 0) {
            return $this->ivLength;
        }

        return $this->getIvLength(
            str_ireplace("-" . $mode, "", $this->cipher)
        );
    }

    /**
     * Get the name of hashing algorithm.
     */
    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }

    /**
     * Checks if a mode (string) is in the values to compare (modes array)
     */
    private function checkIsMode(array $modes, string $mode): bool
    {
        return in_array($mode, $modes);
    }

    protected function decryptGetUnpadded(
        string $mode,
        int $blockSize,
        string $decrypted
    ): string {
        if ($this->checkIsMode(["cbc", "ecb"], $mode)) {
            $padding   = $this->padding;

            $decrypted = $this->cryptUnpadText(
                $decrypted,
                $mode,
                $blockSize,
                $padding
            );
        }

        return $decrypted;
    }

    /**
     * Removes a padding from a text.
     *
     * If the function detects that the text was not padded, it will return it
     * unmodified.
     *
     * @throws Exception
     */
    protected function cryptUnpadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ): string {
        $length      = strlen($input);
        $paddingSize = 0;

        if (
            $length > 0 &&
            ($length % $blockSize === 0) &&
            $this->checkIsMode(["cbc", "ecb"], $mode)
        ) {
            $service = $this->padFactory->padNumberToService($paddingType);
            $paddingSize = $this->padFactory->newInstance($service)
                    ->unpad($input, $blockSize);

            if ($paddingSize > 0) {
                if ($paddingSize <= $blockSize) {
                    if ($paddingSize < $length) {
                        return substr(
                            $input,
                            0,
                            $length - $paddingSize
                        );
                    }

                    return "";
                }

                $paddingSize = 0;
            }
        }

        if (0 === $paddingSize) {
            return $input;
        }

        return "";
    }


    /**
     * Decrypts an encrypted text.
     */
    public function decrypt(string $input, string $key = null): string
    {
        $decryptKey = $this->key;
        if (!empty($key)) {
            $decryptKey = $key;
        }

        if (empty($decryptKey)) {
            throw new Exception("Decryption key cannot be empty");
        }

        $cipher   = $this->cipher;
        $ivLength = $this->ivLength;

        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);
        $iv        = mb_substr($input, 0, $ivLength, "8bit");

        $digest        = "";
        $hashAlgorithm = $this->getHashAlgorithm();

        /**
         * Check if we have chosen signing and use the hash
         */
        if ($this->useSigning) {
            $hashLength = strlen(hash($hashAlgorithm, "", true));
            $digest     = mb_substr($input, $ivLength, $hashLength, "8bit");
            $cipherText = mb_substr($input, $ivLength + $hashLength, null, "8bit");
        }
        else {
            $cipherText = mb_substr($input, $ivLength, null, "8bit");
        }

        $decrypted = $this->decryptGcmCcmAuth(
            $mode,
            $cipherText,
            $decryptKey,
            $iv
        );

        $padded = $decrypted;

        /**
         *  The variable below keeps the string (not unpadded). It will be used
         * to compare the hash if we use a digest (signed)
         */
        $decrypted = $this->decryptGetUnpadded(
            $mode,
            $blockSize,
            $decrypted
        );

        if ($this->useSigning) {
            /**
             * Checks on the decrypted message digest using the HMAC method.
             */
            if ($digest !== hash_hmac($hashAlgorithm, $padded, $decryptKey, true)) {
                throw new Mismatch("Hash does not match.");
            }
        }

        return $decrypted;
    }

    /**
     * Encrypts a text.
     *
     * @throws Exception
     */
    public function encrypt(string $input, string $key = null): string
    {
        $encryptKey = $this->key;
        if (!empty($key)) {
            $encryptKey = $key;
        }

        if (empty($encryptKey)) {
            throw new Exception("Encryption key cannot be empty");
        }

        $cipher   = $this->cipher;
        $ivLength = $this->ivLength;

        $this->checkCipherHashIsAvailable($cipher, "cipher");

        $mode      = $this->getMode();
        $blockSize = $this->getBlockSize($mode);
        $iv        = $this->phpOpensslRandomPseudoBytes($ivLength);

        if ($iv === false) {
            throw new Exception("Cannot calculate Random Pseudo Bytes");
        }

        $padded = $this->encryptGetPadded($mode, $input, $blockSize);

        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        $encrypted = $this->encryptGcmCcm($mode, $padded, $encryptKey, $iv);

        if ($this->useSigning) {
            $digest = hash_hmac(
                $this->getHashAlgorithm(),
                $padded,
                $encryptKey,
                true
            );

            return $iv . $digest . $encrypted;
        }
        return $iv . $encrypted;
    }

    /**
     * @throws Exception
     */
    protected function encryptGetPadded(
        string $mode,
        string $input,
        int $blockSize
    ): string {
        if (
            0 !== $this->padding &&
            $this->checkIsMode(["cbc", "ecb"], $mode)
        ) {
            return $this->cryptPadText($input, $mode, $blockSize, $this->padding);
        }

        return $input;
    }

    /**
     * Pads texts before encryption. See
     * [cryptopad](https://www.di-mgt.com.au/cryptopad.html)
     *
     * @throws Exception
     */
    protected function cryptPadText(
        string $input,
        string $mode,
        int $blockSize,
        int $paddingType
    ): string {
        $padding     = "";
        $paddingSize = 0;

        if ($this->checkIsMode(["cbc", "ecb"], $mode)) {
            $paddingSize = $blockSize - (mb_strlen($input) % $blockSize);

            if ($paddingSize >= 256 || $paddingSize < 0) {
                throw new Exception(
                    "Padding size cannot be less than 0 or greater than 256"
                );
            }

            $service = $this->padFactory->padNumberToService($paddingType);
            $padding = $this->padFactory->newInstance($service)
                    ->pad($paddingSize);
        }

        if (0 === $paddingSize) {
            return $input;
        }

        return $input . substr($padding, 0, $paddingSize);
    }

    /**
     * @throws Exception
     */
    protected function decryptGcmCcmAuth(
        string $mode,
        string $cipherText,
        string $decryptKey,
        string $iv
    ): string {
        $cipher = $this->cipher;

        if ($this->checkIsMode(["ccm", "gcm"], $mode)) {
            $authData      = $this->authData;
            $authTagLength = $this->authTagLength;
            $authTag       = substr($cipherText, -$authTagLength);
            $encrypted     = str_replace($authTag, "", $cipherText);

            $decrypted = openssl_decrypt(
                $encrypted,
                $cipher,
                $decryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag,
                $authData
            );
        }
        else {
            $decrypted = openssl_decrypt(
                $cipherText,
                $cipher,
                $decryptKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        if (!$decrypted) {
            throw new Exception("Could not decrypt data");
        }

        return $decrypted;
    }

    /**
     * Returns if the input length for decryption is valid or not
     * (number of bytes required by the cipher).
     */
    public function isValidDecryptLength(string $input): bool
    {
        $length = $this->phpOpensslCipherIvLength($this->cipher);

        if ($length === false) {
            return false;
        }

        return !!($length <= mb_strlen($input));
    }

    /**
     * Set the name of hashing algorithm.
     *
     * @throws Exception
     */
    public function setHashAlgorithm(string $hashAlgorithm): CryptInterface
    {
        $this->checkCipherHashIsAvailable($hashAlgorithm, "hash");

        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function encryptGcmCcm(
        string $mode,
        string $padded,
        string $encryptKey,
        string $iv
    ): string {
        $cipher  = $this->cipher;
        $authTag = "";

        /**
         * If the mode is "gcm" or "ccm" and auth data has been passed call it
         * with that data
         */
        if ($this->checkIsMode(["ccm", "gcm"], $mode)) {
            $authData = $this->authData;

            if (empty($authData)) {
                throw new Exception(
                    "Auth data must be provided when using AEAD mode"
                );
            }

            $authTag       = $this->authTag;
            $authTagLength = $this->authTagLength;

            $encrypted = openssl_encrypt(
                $padded,
                $cipher,
                $encryptKey,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag,
                $authData,
                $authTagLength
            );

            $this->authTag = $authTag;
        }
        else {
            $encrypted = openssl_encrypt(
                $padded,
                $cipher,
                $encryptKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        if (false === $encrypted) {
            throw new Exception("Could not encrypt data");
        }

        /**
         * Store the tag with encrypted data and return it. In the non AEAD
         * mode this is an empty string
         */
        return $encrypted . $authTag;
    }
}
