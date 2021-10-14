<?php

namespace BertW;

interface ClientInterface
{
    /*
    |--------------------------------------------------------------------------
    | Informational Commands
    |--------------------------------------------------------------------------
    */

    /**
     * Get basic account info of the merchant.
     */
    public function getBasicInfo();

    /**
     * Get exchange rates / coins list.
     * @param null|int $short
     * @param null|int $accepted
     * @return mixed
     */
    public function rates($short = null, $accepted = null);

    /**
     * Get coin balances.
     * @param int $all
     * @return mixed
     */
    public function balances($all = 0);

    /**
     * Get deposit addresses for personal use.
     * @param string $currency
     * @return mixed
     */
    public function getDepositAddress($currency);

    /*
    |--------------------------------------------------------------------------
    | Receiving Payments
    |--------------------------------------------------------------------------
    */

    /**
     * Create a transaction.
     * @param array $options
     * @return mixed
     */
    public function createTransaction(array $options);

    /**
     * Get callback addresses for commercial use.
     * @param string $currency
     * @param null|string $ipn_url
     * @return mixed
     */
    public function getCallbackAddress($currency, $ipn_url = null);

    /**
     * Get information for multiple transactions.
     * @param string $txid
     * @return mixed
     */
    public function getTxInfoMulti($txid);

    /**
     * Get single transaction information.
     * @param string $txid
     * @param null|int $full
     * @return mixed
     */
    public function getTxInfo($txid, $full = null);


    /**
     * Get transaction ids.
     * @param null|int $limit
     * @param null|int $start
     * @param null|int $newer
     * @param null|int $all
     * @return mixed
     */
    public function getTxIds($limit = null, $start = null, $newer = null, $all = null);

    /*
    |--------------------------------------------------------------------------
    | Withdrawals/Transfers
    |--------------------------------------------------------------------------
    */

    /**
     * Create a transfer.
     * @param array $options
     * @return mixed
     */
    public function createTransfer(array $options);

    /**
     * Create a withdrawal.
     * @param $options
     * @return mixed
     */
    public function createWithdrawal(array $options);

    /**
     * Create a mass withdrawal.
     * @param $options
     * @return mixed
     */
    public function createMassWithdrawal(array $options);

    /**
     * Convert a currency.
     * @param array $options
     * @return mixed
     */
    public function convert(array $options);

    /**
     * Get withdrawal information.
     * @param null|int $limit
     * @param null|int $start
     * @param null|int $newer
     * @return mixed
     */
    public function getWithdrawalHistory($limit = null, $start = null, $newer = null);

    /**
     * Get withdrawal information for a withdrawal ID.
     * @param string $id
     * @return mixed
     */
    public function getWithdrawalInfo($id);

    /**
     * Get conversion info for a conversion ID.
     * @param string $id
     * @return mixed
     */
    public function getConversionInfo($id);

    /*
    |--------------------------------------------------------------------------
    | $PayByName
    |--------------------------------------------------------------------------
    */

    /**
     * Get $PayByName profile info.
     * @param string $pbntag
     * @return mixed
     */
    public function getPbnInfo($pbntag);

    /**
     * Get $PayByName tag list.
     * @return mixed
     */
    public function getPbnList();

    /**
     * Update $PayByName profile.
     * @param array $options
     * @return mixed
     */
    public function updatePbnTag(array $options);

    /**
     * Claim $PayByName tag.
     * @param string $tagid
     * @param string $name
     * @return mixed
     */
    public function claimPbnTag($tagid, $name);

}
