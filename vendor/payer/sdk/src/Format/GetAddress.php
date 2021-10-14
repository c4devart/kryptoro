<?php namespace Payer\Sdk\Format;
/**
 * Copyright 2016 Payer Financial Services AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5.3
 *
 * @package   Payer_Sdk
 * @author    Payer <teknik@payer.se>
 * @copyright 2016 Payer Financial Services AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 */

class GetAddress extends DataFormatter
{

    /**
     * Handles the default 'Get Address' object format
     *
     * @param array $getAddress The get address request object to be filtered
     * @return array The filtered get address object
     *
     */
    public function filterGetAddress(array $getAddress) {
        if (!array_key_exists("identity_number", $getAddress)) {
            $getAddress['identity_number'] = '';
        }

        if (!array_key_exists("zip_code", $getAddress)) {
            $getAddress['zip_code'] = '';
        }

        if (!array_key_exists("challenge_token", $getAddress)) {
            $getAddress['challenge_token'] = '';
        }

        return $getAddress;
    }

}