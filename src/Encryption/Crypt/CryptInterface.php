<?php

namespace One23\PhalconPhp\Encryption\Crypt;

/**
 * Interface for Phalcon\Crypt
 */
interface CryptInterface
{
    /**
     * Decrypts a text
     */
    public function decrypt(string $input, string $key = null): string;

    /**
     * Decrypt a text that is coded as a base64 string
     */
    public function decryptBase64(string $input, string $key = null): string;

    /**
     * Encrypts a text
     */
    public function encrypt(string $input, string $key = null): string;

    /**
     * Encrypts a text returning the result as a base64 string
     */
    public function encryptBase64(string $input, string $key = null): string;

    /**
     * Returns a list of available cyphers
     */
    public function getAvailableCiphers(): array;

    /**
     * Returns the authentication tag
     */
    public function getAuthTag(): string;

    /**
     * Returns authentication data
     */
    public function getAuthData(): string;

    /**
     * Returns the authentication tag length
     */
    public function getAuthTagLength(): int;

    /**
     * Returns the current cipher
     */
    public function getCipher(): string;

    /**
     * Returns the encryption key
     */
    public function getKey(): string;

    /**
     * Sets the authentication tag
     */
    public function setAuthTag(string $tag): CryptInterface;

    /**
     * Sets authentication data
     */
    public function setAuthData(string $data): CryptInterface;

    /**
     * Sets the authentication tag length
     */
    public function setAuthTagLength(int $length): CryptInterface;

    /**
     * Sets the cipher algorithm
     */
    public function setCipher(string $cipher): CryptInterface;

    /**
     * Sets the encryption key
     */
    public function setKey(string $key): CryptInterface;

    /**
     * Changes the padding scheme used.
     */
    public function setPadding(int $scheme): CryptInterface;

    /**
     * Sets if the calculating message digest must be used.
     */
    public function useSigning(bool $useSigning): CryptInterface;
}
