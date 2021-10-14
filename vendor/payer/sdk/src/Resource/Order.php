<?php namespace Payer\Sdk\Resource;
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


use Payer\Sdk\PayerGatewayInterface;
use Payer\Sdk\Exception\InvalidRequestException;
use Payer\Sdk\Exception\WebserviceException;
use Payer\Sdk\Format\Order as OrderFormatter;
use Payer\Sdk\Validation\Order as OrderValidator;
use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Transport\Http;
use Payer\Sdk\Webservice\WebserviceInterface;

class Order extends PayerResource
{

    /**
     * Data Formatter
     *
     * @var DataFormatter
     *
     */
    private $_formatter;

    /**
     * Data Validator
     *
     * @var ResourceValidator
     *
     */
    private $_validator;

    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->gateway = $gateway;

        $this->_formatter = new OrderFormatter;
        $this->_validator = new OrderValidator;
    }

    /**
     * Creates an order
     *
     * @param array $input The order creation request object
     * @return string The id of the created order as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function create(array $input)
    {
        $order = $this->_formatter->filterOrder($input);

        $customer = $order['customer'];
        if (empty($customer)) {
            throw new InvalidRequestException("Missing argument: 'customer'");
        }

        $identityNumber = $order['customer']['identity_number'];
        if (empty($identityNumber)) {
            throw new InvalidRequestException("Missing argument: 'identity_number' in 'customer'");
        }

        $items = $order['items'];
        if (empty($items)) {
            throw new InvalidRequestException("Missing argument: 'items'");
        }

        $this->_validator->validateOrder($order);

        $options = $order['options'];

        $orderDetails = $this->_handleOrderDetailsFormat($order);
        $orderItems = $this->_handleOrderItemFormat($items);
        $orderOptions = $this->_handleOrderOptionsFormat($options);

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $orderId = $soap->createOrder(
            $orderDetails,
            $orderItems,
            $orderOptions
        );
        $soap->close();

        return array(
            'order_id' => $orderId
        );
    }

    /**
     * Commits and transfers the order to an invoice
     *
     * @param array $input The commit order request object
     * @return string The number of the created invoice as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function commit(array $input)
    {
        $input = $this->_formatter->filterCommit($input);

        $orderId = $input['order_id'];
        $referenceId = $input['reference_id'];

        if (empty($orderId) && empty($referenceId)) {
            throw new InvalidRequestException("Missing argument: 'order_id' or 'reference_id");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();

        if (!empty($referenceId)) {
            $invoiceNumber = $soap->commitOrderByReference($referenceId);
        } else {
            $invoiceNumber = $soap->commitOrder($orderId);
        }

        $soap->close();

        return array(
            'invoice_number' => $invoiceNumber
        );
    }

    /**
     * Get the current state of an order
     *
     * @param array $input The get status request object
     * @return string The order status object as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function getStatus(array $input)
    {
        $input = $this->_formatter->filterGetStatus($input);

        $orderId = $input['order_id'];
        if (empty($orderId)) {
            throw new InvalidRequestException("Missing argument: 'order_id'");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $orderStatus = (array) $soap->orderStatus($orderId);
        $soap->close();

        return array(
            'status'            => $orderStatus['Status'],
            'create_date'       => $orderStatus['OrderCreateDate'],
            'order_id'          => $orderStatus['OrderId'],
            'order_number'      => $orderStatus['OrderNumber'],
            'order_total'       => $orderStatus['OrderTotal'],
            'delivered_total'   => $orderStatus['DeliveredTotal'],
            'delivered_vat'     => $orderStatus['DeliveredVat'],
            'options'           => $orderStatus['Options'],
            'customer' => array(
                'id'   => $orderStatus['MerchantCustomerId'],
                'user_id'       => $orderStatus['UserId']
            ),
            'invoice' => array(
                'invoice_number'    => $orderStatus['InvoiceNumber'],
                'create_date'       => $orderStatus['InvoiceCreateDate'],
                'invoice_date'      => $orderStatus['InvoiceDate'],
                'due_date'          => $orderStatus['InvoiceDueDate']
            )
        );
    }

    /**
     * Converts the Order Details to Payer format
     *
     * @param array $order The order array to be formatted
     * @return array The formatted order object
     *
     */
    private function _handleOrderDetailsFormat(array $order)
    {
        $customer = $order['customer'];
        return array(
            'OrderNumber'   => $order['reference_id'],
            'Description'   => $order['description'],
            'Message'       => $order['message'],
            'ClientIp'      => $order['client_ip'],
            'IsTest'        => $order['test_mode'],
            'Currency'      => $order['currency'],

            'CustomerId'    => $customer['id'],
            'Company'       => $customer['organisation'],
            'YourReference' => $customer['your_reference'],
            'PersonalId'    => $customer['identity_number'],
            'FirstName'     => $customer['first_name'],
            'LastName'      => $customer['last_name'],
            'invoiceAddress' => array(
                'Address1'  => ( !empty( $customer['address']['co'] ) ? 'c/o ' . $customer['address']['co'] : $customer['address']['address_1'] ),
                'Address2'  => ( !empty( $customer['address']['co'] ) ? $customer['address']['address_1'] : $customer['address']['address_2'] ),
                'ZipCode'   => $customer['zip_code'],
                'City'      => $customer['city'],
                'CountryId' => $customer['country_code']
            ),
            'Phone'     => $customer['phone']['home'],
            'CellPhone' => $customer['phone']['mobile'],
            'Email'     => $customer['email'],
            'CoAddress' => $customer['address']['co'],
            'Address'   => $customer['address']['address_1'],
            'ZipCode'   => $customer['zip_code'],
            'Town'      => $customer['city'],
            'CountryId' => $customer['country_code'],
        );
    }

    /**
     * Converts the Order Items to Payer format
     *
     * @param array $items The items array to be formatted
     * @return array The formatted items object
     *
     */
    private function _handleOrderItemFormat(array $items)
    {
        $orderItems = array();
        foreach ($items as $item) {
            $orderItems[] = array(
                'EntryType'         => strtoupper($item['type']),
                'Position'          => $item['line_number'],
                'ItemNumber'        => $item['article_number'],
                'Description'       => $item['description'],
                'Unit'              => $item['unit'],
                'UnitQty'           => $item['quantity'],
                'Price'             => $item['unit_price'],
                'VatPercentage'     => $item['unit_vat_percentage'],
                'Account'           => $item['account'],
                'AgentId'           => $item['dist_agent_id']
            );
         }
        return $orderItems;
    }

    /**
     * Converts the Options array to Payer format
     *
     * @param array $options The options array to be formatted
     * @return null|string The formatted options object
     *
     */
    private function _handleOrderOptionsFormat(array $options)
    {
        if (!empty($options)) {
            $orderOptions = '';
            if (!empty($options['delivery_type'])) {
                $orderOptions .= (empty($orderOptions) ? '' : ',') . 'deliverytype=' . $options['delivery_type'];
            }
            if (!empty($options['template_type'])) {
                $orderOptions .= (empty($orderOptions) ? '' : ',') . 'templatetype=' . $options['template_type'];
            }
            if (!empty($options['style'])) {
                $orderOptions .= (empty($orderOptions) ? '' : ',') . 'style=' . $options['style'];
            }
            return $orderOptions;
        }
        return null;
    }

}