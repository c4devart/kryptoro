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

class Credential extends DataFormatter
{

    /**
     * Handles the default 'Webservice Credentials' object format
     *
     * @param array $credentials The webservice credentials object to be filtered
     * @return array The filtered credentials object
     *
     */
    public function filterCredentials(array $credentials)
    {
        if (!array_key_exists("agent_id", $credentials)) {
            $credentials['agent_id'] = '';
        }

        if (array_key_exists("post", $credentials) && is_array($credentials['post'])) {
            if (!array_key_exists("key_1", $credentials['post'])) {
                $credentials['post']['key_1'] = '';
            }

            if (!array_key_exists("key_2", $credentials['post'])) {
                $credentials['post']['key_2'] = '';
            }

            if (!array_key_exists("redirect_path", $credentials['post'])) {
                $credentials['post']['redirect_path'] = '';
            }

            if (!array_key_exists("version", $credentials['post'])) {
                $credentials['post']['version'] = '';
            }
        }

        if (array_key_exists("rest", $credentials) && is_array($credentials['rest'])) {
            if (!array_key_exists("username", $credentials['rest'])) {
                $credentials['rest']['username'] = '';
            }

            if (!array_key_exists("password", $credentials['rest'])) {
                $credentials['rest']['password'] = '';
            }
        }

        if (array_key_exists("soap", $credentials) && is_array($credentials['soap'])) {
            if (!array_key_exists("username", $credentials['soap'])) {
                $credentials['soap']['username'] = '';
            }

            if (!array_key_exists("password", $credentials['soap'])) {
                $credentials['soap']['password'] = '';
            }
        }

        return $credentials;
    }

}