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

class Order extends DataFormatter
{

    /**
     * Handles the default 'Customer Details' object format
     *
     * @param array $customer The customer details request object to be filtered
     * @return array The filtered customer details object
     *
     */
    public function filterCustomerDetails(array $customer)
    {
        if (!array_key_exists("id", $customer)) {
            $customer['id'] = '';
        }

        if (!array_key_exists("identity_number", $customer)) {
            $customer['identity_number'] = '';
        }

        if (!array_key_exists("organisation", $customer)) {
            $customer['organisation'] = '';
        }

        if (!array_key_exists("your_reference", $customer)) {
            $customer['your_reference'] = '';
        }

        if (!array_key_exists("first_name", $customer)) {
            $customer['first_name'] = '';
        }

        if (!array_key_exists("last_name", $customer)) {
            $customer['last_name'] = '';
        }

        if (!array_key_exists("address", $customer)) {
            $customer['address'] = array();
        }

        if (!array_key_exists("co", $customer['address'])) {
            $customer['address']['co'] = '';
        }

        if (!array_key_exists("address_1", $customer['address'])) {
            $customer['address']['address_1'] = '';
        }

        if (!array_key_exists("address_2", $customer['address'])) {
            $customer['address']['address_2'] = '';
        }

        if (!array_key_exists("zip_code", $customer)) {
            $customer['zip_code'] = '';
        }

        if (!array_key_exists("city", $customer)) {
            $customer['city'] = '';
        }

        if (!array_key_exists("country_code", $customer)) {
            $customer['country_code'] = '';
        }

        if (!array_key_exists("email", $customer)) {
            $customer['email'] = '';
        }

        if (!array_key_exists("phone", $customer)) {
            $customer['phone'] = array();
        }

        if (!array_key_exists("home", $customer['phone'])) {
            $customer['phone']['home'] = '';
        }

        if (!array_key_exists("work", $customer['phone'])) {
            $customer['phone']['work'] = '';
        }

        if (!array_key_exists("mobile", $customer['phone'])) {
            $customer['phone']['mobile'] = '';
        }

        return $customer;
    }

    /**
     * Handles the default 'Commit Order' object format
     *
     * @param array $commit The commit request object to be filtered
     * @return array The filtered commit object
     *
     */
    public function filterCommit(array $commit) {
        if (!array_key_exists("order_id", $commit)) {
            $commit['order_id'] = '';
        }

        if (!array_key_exists("reference_id", $commit)) {
            $commit['reference_id'] = '';
        }

        return $commit;
    }

    /**
     * Handles the default 'Get Status' object format
     *
     * @param array $getStatus The get status request object to be filtered
     * @return array The filtered get status object
     *
     */
    public function filterGetStatus(array $getStatus) {
        if (!array_key_exists("order_id", $getStatus)) {
            $getStatus['order_id'] = '';
        }

        return $getStatus;
    }

    /**
     * Handles the default 'Order Items' object format
     *
     * @param array $items The items request object to be filtered
     * @return array The filtered items object
     *
     */
    public function filterItems(array $items)
    {
        $index = 0;
        foreach ($items as $item) {
            if (!array_key_exists("type", $item)) {
                $item['type'] = 'FREEFORM';
            }
            $item['type'] = strtoupper($item['type']);

            if (!array_key_exists("line_number", $item)) {
                $item['line_number'] = ($index + 1);
            }

            if (!array_key_exists("description", $item)) {
                $item['description'] = '';
            }

            if (!array_key_exists("article_number", $item)) {
                $item['article_number'] = '';
            }

            if (!array_key_exists("unit_price", $item)) {
                $item['unit_price'] = '';
            }

            if (!array_key_exists("unit_vat_percentage", $item)) {
                $item['unit_vat_percentage'] = 25;
            }

            if (!array_key_exists("quantity", $item)) {
                $item['quantity'] = 1;
            }

            if (!array_key_exists("unit", $item)) {
                $item['unit'] = '';
            }

            if (!array_key_exists("account", $item)) {
                $item['account'] = '';
            }

            if (!array_key_exists("dist_agent_id", $item)) {
                $item['dist_agent_id'] = '';
            }

            $items[$index++] = $item;
        }

        return $items;
    }

    /**
     * Handles the default 'ORder Details' object format
     *
     * @param array $order The order details request object to be filtered
     * @return array The filtered get status object
     *
     */
    public function filterOrder(array $order)
    {
        if (!array_key_exists("charset", $order)) {
            $order['charset'] = 'ISO-8859-1';
        }

        if (!array_key_exists("client_ip", $order)) {
            $order['client_ip'] = '';
        }

        if (!array_key_exists("currency", $order)) {
            $order['currency'] = 'SEK';
        }

        if (!array_key_exists("description", $order)) {
            $order['description'] = date('Y-m-d H:i:s');
        }

        if (!array_key_exists("items", $order)) {
            $order['items'] = array();
        }
        $order['items'] = $this->filterItems($order['items']);

        if (!array_key_exists("message", $order)) {
            $order['message'] = '';
        }

        if (!array_key_exists("options", $order)) {
            $order['options'] = array();
        }
        $order['options'] = $this->filterOptions($order['options']);

        if (!array_key_exists("reference_id", $order)) {
            if (array_key_exists("order_number", $order)) {
                $order['reference_id'] = $order['order_number']; // Added support for < v1.1.2
            } else {
                $order['reference_id'] = '';
            }
        }

        if (!array_key_exists("test_mode", $order)) {
            $order['test_mode'] = false;
        }

        $order['customer'] = $this->filterCustomerDetails($order['customer']);

        return $order;
    }

    /**
     * Handles the default 'Order Options' object format
     *
     * @param array $options The options request object to be filtered
     * @return array The filtered options object
     *
     */
    public function filterOptions(array $options)
    {
        if (!array_key_exists("delivery_type", $options)) {
            $options['delivery_type'] = '';
        }

        if (!array_key_exists("template_type", $options)) {
            $options['template_type'] = '';
        }

        if (!array_key_exists("style", $options)) {
            $options['style'] = '';
        }

        return $options;
    }


}