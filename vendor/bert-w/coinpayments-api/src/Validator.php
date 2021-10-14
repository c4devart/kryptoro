<?php

namespace BertW;

use BertW\Exception\ValidationException;

/**
 * Validator class that validates the input for the API routes. Simply call Validator::create('cmd_name', $inputArray)
 * to create a validator object, and call ->validate() to either have an exception thrown for invalid input, or have an
 * array returned, containing the validated data.
 */
class Validator
{
    /** @var array */
    const COMMANDS = [
        'get_basic_info' => [
            // No params.
        ],
        'rates' => [
            'optional' => ['short', 'accepted']
        ],
        'balances' => [
            'required' => ['all']
        ],
        'get_deposit_address' => [
            'required' => ['currency'],
        ],
        'create_transaction' => [
            'required' => ['amount', 'currency1', 'currency2'],
            'optional' => ['address', 'buyer_email', 'buyer_name', 'item_name', 'item_number', 'invoice', 'custom', 'ipn_url']
        ],
        'get_callback_address' => [
            'required' => ['currency'],
            'optional' => ['ipn_url']
        ], 'get_tx_info_multi' => [
            'required' => ['txid']
        ], 'get_tx_info' => [
            'required' => ['txid'],
            'optional' => ['full']
        ], 'get_tx_ids' => [
            'optional' => ['limit', 'start', 'newer', 'all']
        ], 'create_transfer' => [
            'required' => ['amount', 'currency'],
            'optional' => ['merchant', 'pbntag', 'auto_confirm']
        ], 'create_withdrawal' => [
            'required' => ['amount', 'currency'],
            'optional' => ['currency2', 'address', 'pbntag', 'dest_tag', 'ipn_url', 'auto_confirm', 'note']
        ], 'create_mass_withdrawal' => [
            'required' => ['wd']
        ], 'convert' => [
            'required' => ['amount', 'from', 'to'],
            'optional' => ['address', 'dest_tag']
        ], 'get_withdrawal_history' => [
            'required' => ['limit', 'start', 'newer']
        ], 'get_withdrawal_info' => [
            'required' => ['id']
        ], 'get_conversion_info' => [
            'required' => ['id'],
        ], 'get_pbn_info' => [
            'required' => ['pbn_tag'],
        ], 'get_pbn_list' => [
            // No params.
        ], 'update_pbn_tag' => [
            'required' => ['tagid'],
            'optional' => ['name', 'email', 'url', 'image']
        ], 'claim_pbn_tag' => [
            'required' => ['tagid', 'name'],
        ]
    ];

    /** @var string */
    protected static $name;

    /** @var array */
    protected static $required = [];

    /** @var array */
    protected static $optional = [];

    private function __construct($name, $input, array $required = [], array $optional = [])
    {
        foreach ($input as $key => $value) {
            $this->{$key} = $value;
        }
        $this::$name = $name;
        $this::$required = $required;
        $this::$optional = $optional;
    }

    /**
     * @return array
     * @throws ValidationException
     */
    public function validate()
    {
        $this->checkProperties();
        return (array)$this;
    }

    /**
     * @return boolean
     * @throws ValidationException
     */
    private function checkProperties()
    {
        // Check if required properties have been set.
        foreach ($this::$required as $requiredProperty) {
            if (!property_exists($this, $requiredProperty) || $this->{$requiredProperty} === null) {
                throw new ValidationException('Property "' . $requiredProperty . '" is required for ' . $this::$name . '.');
            }
        }

        // Check if properties are either in the optional or required list.
        foreach (get_object_vars($this) as $property => $value) {
            if (!in_array($property, $this::$required) && !in_array($property, $this::$optional)) {
                throw new ValidationException('"' . $property . '" is not a valid property for ' . $this::$name . '.');
            }
        }
        return true;
    }

    /**
     * Create a validator for a certain API command.
     * @param string $cmd
     * @param array $input
     * @return Validator
     * @throws ValidationException
     */
    public static function create($cmd, $input)
    {
        if (!array_key_exists($cmd, self::COMMANDS)) {
            throw new ValidationException('Validator for "' . $cmd . '" does not exist.');
        }
        $command = self::COMMANDS[$cmd];
        return new self(
            $cmd,
            $input,
            key_exists('required', $command) ? $command['required'] : [],
            key_exists('optional', $command) ? $command['optional'] : []);
    }
}
