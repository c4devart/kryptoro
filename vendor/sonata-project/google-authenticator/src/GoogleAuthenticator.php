<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at.
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Authenticator;

final class GoogleAuthenticator
{
    /**
     * @var int
     */
    private $passCodeLength;

    /**
     * @var int
     */
    private $secretLength;

    /**
     * @var int
     */
    private $pinModulo;

    /**
     * NEXT_MAJOR: remove this property.
     */
    private $fixBitNotation;

    /**
     * @param int $passCodeLength
     * @param int $secretLength
     */
    public function __construct(int $passCodeLength = 6, int $secretLength = 10)
    {
        $this->passCodeLength = $passCodeLength;
        $this->secretLength = $secretLength;
        $this->pinModulo = pow(10, $this->passCodeLength);
    }

    /**
     * @param $secret
     * @param $code
     *
     * @return bool
     */
    public function checkCode($secret, $code)
    {
        $time = floor(time() / 30);
        for ($i = -1; $i <= 1; ++$i) {
            if ($this->codesEqual($this->getCode($secret, $time + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $secret
     * @param null $time
     *
     * @return string
     */
    public function getCode($secret, $time = null)
    {
        if (!$time) {
            $time = floor(time() / 30);
        }

        $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true);
        $secret = $base32->decode($secret);

        $time = pack('N', $time);
        $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord(substr($hash, -1));
        $offset = $offset & 0xF;

        $truncatedHash = $this->hashToInt($hash, $offset) & 0x7FFFFFFF;
        $pinValue = str_pad($truncatedHash % $this->pinModulo, 6, '0', STR_PAD_LEFT);

        return $pinValue;
    }

    /**
     * NEXT_MAJOR: Add a new parameter called $issuer.
     *
     * @param string $user
     * @param string $hostname
     * @param string $secret
     *
     * @return string
     */
    public function getUrl($user, $hostname, $secret)
    {
        $args = func_get_args();
        $encoder = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=';
        $urlString = '%sotpauth://totp/%s@%s%%3Fsecret%%3D%s'.(array_key_exists(3, $args) && !is_null($args[3]) ? ('%%26issuer%%3D'.$args[3]) : '');
        $encoderURL = sprintf($urlString, $encoder, $user, $hostname, $secret);

        return $encoderURL;
    }

    /**
     * @return string
     */
    public function generateSecret()
    {
        $secret = random_bytes($this->secretLength);

        $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true);

        return $base32->encode($secret);
    }

    /**
     * @param string $bytes
     * @param int    $start
     *
     * @return int
     */
    private function hashToInt(string $bytes, int $start): int
    {
        $input = substr($bytes, $start, strlen($bytes) - $start);
        $val2 = unpack('N', substr($input, 0, 4));

        return $val2[1];
    }

    /**
     * A constant time code comparison.
     *
     * @param string $known known code
     * @param string $given code received from a user
     *
     * @return bool
     *
     * @see http://codereview.stackexchange.com/q/13512/6747
     */
    private function codesEqual(string $known, string $given): bool
    {
        if (strlen($given) !== strlen($known)) {
            return false;
        }

        $res = 0;

        $knownLen = strlen($known);

        for ($i = 0; $i < $knownLen; ++$i) {
            $res |= (ord($known[$i]) ^ ord($given[$i]));
        }

        return $res === 0;
    }
}
