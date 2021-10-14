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

class Invoice extends DataFormatter
{
    /**
     * Handles the default 'Invoice Activation' object format
     *
     * @param array $activation The invoice activation request object to be filtered
     * @return array The filtered invoice activation object
     *
     */
    public function filterActivation(array $activation)
    {
        if (!array_key_exists("invoice_number", $activation)) {
            $activation['invoice_number'] = '';
        }

        if (!array_key_exists("options", $activation)) {
            $activation['options'] = array();
        }

        if (!array_key_exists("delivery_type", $activation)) {
            $activation['options']['delivery_type'] = '';
        }

        return $activation;
    }

    /**
     * Handles the default 'Template Binding' object format
     *
     * @param array $binding The template binding request object to be filtered
     * @return array The filtered template binding object
     *
     */
    public function filterBinding(array $binding)
    {
        if (!array_key_exists("invoice_number", $binding)) {
            $binding['invoice_number'] = '';
        }

        if (!array_key_exists("entry_id", $binding)) {
            $binding['entry_id'] = '';
        }

        return $binding;
    }

    /**
     * Handles the default 'Get Status' object format
     *
     * @param array $getStatus The get invoice status request object to be filtered
     * @return array The filtered get invoice status object
     *
     */
    public function filterGetStatus(array $getStatus)
    {
        if (!array_key_exists("invoice_number", $getStatus)) {
            $getStatus['invoice_number'] = '';
        }

        return $getStatus;
    }

    /**
     * Handles the default 'Invoice Options' object format
     *
     * @param array $options The invoice options request object to be filtered
     * @return array The filtered invoice options object
     *
     */
    public function filterOptions(array $options)
    {
        if (!array_key_exists("delivery_type", $options)) {
            $options['delivery_type'] = '';
        }

        return $options;
    }

    /**
     * Handles the default 'Send Invoice' object format
     *
     * @param array $sendInvoice The send invoice request object to be filtered
     * @return array The filtered send invoice object
     *
     */
    public function filterSendInvoice(array $sendInvoice)
    {
        if (!array_key_exists("invoice_number", $sendInvoice)) {
            $sendInvoice['invoice_number'] = '';
        }

        if (!array_key_exists("options", $sendInvoice)) {
            $sendInvoice['options'] = array();
        }

        if (!array_key_exists("delivery_type", $sendInvoice['options'])) {
            $sendInvoice['options']['delivery_type'] = '';
        }

        if (!array_key_exists("template_entry_id", $sendInvoice['options'])) {
            $sendInvoice['options']['template_entry_id'] = '';
        }

        return $sendInvoice;
    }

}