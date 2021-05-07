<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

class pinax_encryption_Encrypter implements pinax_interfaces_Encrypter
{
    private $keyPath;
    private $key;

    function __construct($keyPath)
	{
        $this->keyPath = $keyPath;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function encrypt($data)
    {
        try {
            return Crypto::encrypt(serialize($data), $this->encryptionKey());
        } catch (\Defuse\Crypto\Exception\CryptoException $ex) {
            throw pinax_encryption_Exception::encryptException();
        }
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decrypt($data)
    {
        try {
            return unserialize(Crypto::decrypt($data, $this->encryptionKey()));
        } catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
            throw pinax_encryption_Exception::decryptException();
        }

        return null;
    }

    /**
     * @return Defuse\Crypto\Key
     */
    private function encryptionKey()
    {
        if (!$this->key) {
            $this->key = Key::loadFromAsciiSafeString($this->readKey());
        }
        return $this->key;
    }

    private function readKey()
    {
        if (!file_exists($this->keyPath)) {
            $key = Key::createNewRandomKey();
            $str = $key->saveToAsciiSafeString();
            file_put_contents($this->keyPath, $str);
            return $str;
        }

        return file_get_contents($this->keyPath);
    }
}
