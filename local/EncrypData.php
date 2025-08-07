<?php  
   
class EncrypData_{
    private $Key;
    private $Salt;
    /**
     * Undocumented function
     *
     * @param [type] $key
     */
    public function __construct($key)
    {
        $this->key  = $this->CrateKey($key);
        $this->Salt = $this->CrateSalt($key);
    }
    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function cadenaDecrypt($value){
        $hash = hash_pbkdf2("sha1", $this->key, mb_convert_encoding($this->Salt, 'UTF-16LE'), 1000, 32, true); 

        $key = substr($hash, 0, 24);
        $iv = substr($hash, 24, 8);
        return iconv('UTF-16', 'UTF-8', openssl_decrypt($value, 'des-ede3-cbc', $key, 0, $iv));
    }
    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function cadenaEncrypt($value){
        $hash = hash_pbkdf2("sha1", $this->key, mb_convert_encoding($this->Salt, 'UTF-16LE'), 1000, 32, true); 

        $key = substr($hash, 0, 24);
        $iv = substr($hash, 24, 8);
        return openssl_encrypt(iconv('UTF-8', 'UTF-16', $value), 'des-ede3-cbc', $key, 0, $iv);
    }
    /**
     * Undocumented function
     *
     * @param [type] $KeyBase
     * @return void
     */
    public function CrateKey($KeyBase){
        return $KeyBase."56".strrev($KeyBase)."A9HHh";
    }
    /**
     * Undocumented function
     *
     * @param [type] $KeyBase
     * @return void
     */
    public function CrateSalt($KeyBase){
        return $KeyBase."12".strrev($KeyBase)."4576sdv";
    }
}