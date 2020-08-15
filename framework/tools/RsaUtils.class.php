<?php


namespace framework\tools;


class RsaUtils
{
    /**
     * 生成公钥私钥：
     * 1.安装openssl
     * 2.生成RSA私钥，生成一个1024bit（128byte）的密钥：
     *      openssl genrsa -out rsa_private_key.pem 1024
     * 3.私钥转换成pkcs8格式：
     *      openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem
     * 4.生成公钥：
     *      openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem
     */

    /**
     * 公钥 私钥路径
     * @var array
     */
    private $_config = [
        'public_key' => '',
        'private_key' => '',
    ];

    public function __construct($private_key_filepath, $public_key_filepath) {
        $this->_config['private_key'] = $this->_getContents($private_key_filepath);
        $this->_config['public_key'] = $this->_getContents($public_key_filepath);
    }

    /**
     * @uses 获取文件内容
     * @param $file_path string
     * @return bool|string
     */
    private function _getContents($file_path) {
        file_exists($file_path) or die ('密钥或公钥的文件路径错误');
        return file_get_contents($file_path);
    }

    /**
     * @uses 获取私钥
     * @return bool|resource
     */
    private function _getPrivateKey() {
        $priv_key = $this->_config['private_key'];
        return openssl_pkey_get_private($priv_key);
    }

    /**
     * @uses 获取公钥
     * @return bool|resource
     */
    private function _getPublicKey() {
        $public_key = $this->_config['public_key'];
        return openssl_pkey_get_public($public_key);
    }

    /**
     * @uses 私钥加密
     * @param string $data
     * @return null|string
     */
    public function privEncrypt($data = '') {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, $this->_getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * @uses 公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicEncrypt($data = '') {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, $this->_getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * @uses 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function privDecrypt($encrypted = '') {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->_getPrivateKey())) ? $decrypted : null;
    }

    /**
     * @uses 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function publicDecrypt($encrypted = '') {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->_getPublicKey())) ? $decrypted : null;
    }
}