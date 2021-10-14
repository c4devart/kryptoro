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
use Payer\Sdk\Format\Invoice as InvoiceFormatter;
use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Transport\Http;

class Invoice extends PayerResource
{

    /**
     * Invoice object formatter
     *
     * @var DataFormatter
     *
     */
    private $_formatter;

    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->gateway = $gateway;

        $this->_formatter = new InvoiceFormatter;
    }

    /**
     * Activates an invoice
     *
     * @param array $input The invoice activation request object
     * @return string The number of the activated invoice as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function activate(array $input)
    {
        $input = $this->_formatter->filterActivation($input);

        $invoiceNumber = $input['invoice_number'];
        $options = $input['options'];

        if (empty($invoiceNumber)) {
            throw new InvalidRequestException("Missing argument: 'invoice_number'");
        }

        $options = $this->_handleInvoiceOptionsFormat($options);

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $invoiceNumber = $soap->invoiceActivation(
            $invoiceNumber,
            false,
            $options
        );
        $soap->close();

        return array(
            'invoice_number' => $invoiceNumber,
        );
    }

    /**
     * Binds an non-activated invoice to a specific template entry.
     *
     * For further information about how you can get started with this, please contact the Payer support.
     *
     * @param array $input The template binding request object
     * @return string The processed binding object as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function bindToTemplateEntry(array $input)
    {
        $input = $this->_formatter->filterBinding($input);

        $invoiceNumber = $input['invoice_number'];
        if (empty($invoiceNumber)) {
            throw new InvalidRequestException("Missing argument: 'invoice_number'");
        }

        $entryId = $input['template_entry_id'];
        if (empty($entryId)) {
            throw new InvalidRequestException("Missing argument: 'template_entry_id'");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $binding = (array) $soap->bindInvoiceToTemplateEntry(
            $invoiceNumber,
            $entryId
        );
        $soap->close();

        return array(
            'template_entry_binding_id' => $binding['Id'],
            'create_date'               => $binding['CreateDate']
        );
    }

    /**
     * Get the current state of an invoice
     *
     * @param array $input The get status request object
     * @return string The invoice status object as json
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function getStatus(array $input)
    {
        $input = $this->_formatter->filterGetStatus($input);

        $invoiceNumber = $input['invoice_number'];
        if (empty($invoiceNumber)) {
            throw new InvalidRequestException("Missing argument: 'invoice_number'");
        }

        $soap = $this->gateway->getSoapService();

        $soap->start();
        $invoiceStatus = (array) $soap->invoiceStatus($invoiceNumber);
        $soap->close();

        $invoiceEvents = array();
        if (array_key_exists("eventHistory", $invoiceStatus)) {
        $invoiceEvents = $this->_handleInvoiceStatusEvent($invoiceStatus['eventHistory']);
        }

        return array(
            'order_number'      => $invoiceStatus['OrderNumber'],
            'transaction_id'    => intval($invoiceStatus['ChargeLogId']),
            'customer' => array(
                'id'            => $invoiceStatus['MerchantCustomerId'],
                'user_id'       => $invoiceStatus['UserId']
            ),
            'total_amount'      => doubleval($invoiceStatus['InvoiceAmount']),
            'rounding_amount'   => doubleval($invoiceStatus['RoundingAmount']),
            'to_pay_amount'     => doubleval($invoiceStatus['ToPayAmount']),
            'invoice_date'      => $invoiceStatus['InvoiceDate'],
            'due_date'          => $invoiceStatus['DueDate'],
            'paid_date'         => $invoiceStatus['PaidDate'],
            'events'            => $invoiceEvents,
            'options'           => $invoiceStatus['Options'],
            'delivery_type'     => $invoiceStatus['DeliveryType']
        );
    }

    /**
     * Returns all available template entries for further template binding
     *
     * @return string An array of template entries as json
     * @throws WebserviceException
     *
     */
    public function getActiveTemplateEntries()
    {
        $soap = $this->gateway->getSoapService();

        $soap->start();
        $templateEntries = (array) $soap->getActiveInvoiceTemplateEntries();
        $soap->close();

        return array(
            'template_entries' => $this->_handleTemplateEntryFormat($templateEntries)
        );
    }

    /**
     * Sends a copy of an invoice with the requested delivery type.
     *
     * @param array $input The send invoice request object
     * @return string The send queue process id
     * @throws InvalidRequestException
     * @throws WebserviceException
     *
     */
    public function send(array $input)
    {
        $input = $this->_formatter->filterSendInvoice($input);

        $invoiceNumber = $input['invoice_number'];

        $options = $input['options'];
        $deliveryType = $options['delivery_type'];

        if (empty($invoiceNumber)) {
            throw new InvalidRequestException("Missing argument: 'invoice_number'");
        }

        if (empty($deliveryType)) {
            throw new InvalidRequestException("Missing argument: 'delivery_type' in 'options'");
        }

        $soap = $this->gateway->getSoapService();

        $options = $this->_handleInvoiceOptionsFormat($options);

        $soap->start();
        $returnCode = $soap->sendInvoice(
            $invoiceNumber,
            $options
        );
        $soap->close();

        return array(
            'process_id' => $returnCode
        );
    }

    /**
     * Converts the Invoice Options array to Payer format
     *
     * @param array $options The invoice options array to be formatted
     * @return null|string The formatted invoice options object
     *
     */
    private function _handleInvoiceOptionsFormat(array $options)
    {
        if (!empty($options)) {
            $orderOptions = '';
            if (!empty($options['delivery_type'])) {
                $orderOptions .= (empty($orderOptions) ? '' : ',') . 'deliverytype=' . $options['delivery_type'];
            }
            if (!empty($options['template_entry_id'])) {
                $orderOptions .= (empty($orderOptions) ? '' : ',') . 'campaignid=' . $options['template_entry_id'];
            }
            return $orderOptions;
        }
        return null;
    }

    /**
     * Converts the Invoice Status Events array to Payer format
     *
     * @param array $events The invoice status events array to be formatted
     * @return null|string The formatted invoice status events object
     *
     */
    private function _handleInvoiceStatusEvent(array $events)
    {
        $formattedEvents = array();
        foreach ($events as $event) {
            $event = (array) $event;
            $formattedEvents = array(
                'id'            => $event['EventId'],
                'type'          => $event['EventType'],
                'create_date'   => $event['EventDate'],
                'process_date'  => $event['ProcessingDate'],
                'amount'        => $event['EventAmount']
            );
        }
        return $formattedEvents;
    }

    /**
     * Converts the Template Entries array to Payer format
     *
     * @param array $templateEntries The template entries array to be formatted
     * @return array The formatted template entries object
     *
     */
    private function _handleTemplateEntryFormat(array $templateEntries)
    {
        $formattedTemplateEntries = array();
        foreach ($templateEntries as $templateEntry) {
            $templateEntry = (array) $templateEntry;
            $formattedTemplateEntries[] = array(
                'template_entry_id'  => $templateEntry['Id'],
                'start_date'         => $templateEntry['StartDate'],
                'end_date'           => $templateEntry['EndDate'],
                'create_date'        => $templateEntry['CreateDate'],
                'update_date'        => $templateEntry['UpdateDate']
            );
        }
        return $formattedTemplateEntries;
    }

}