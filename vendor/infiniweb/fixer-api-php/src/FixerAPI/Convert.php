<?php
namespace InfiniWeb\FixerAPI;

final class Convert
{

    protected $endpointKey = 'convert';

    protected $fixer;

    public function __construct(Fixer $fixer)
    {
        $this->fixer = $fixer;
    }

    /**
     * Convert the specified amount from a currency to another
     * @param  string $from   ISO currency code of the provided amount
     * @param  string $to     ISO Currency code to convert to
     * @param  float $amount  The amount to be converted
     * @param  null|string $date   At the format YYYY-MM-DD, if provided, get the conversion at the specified date
     * @return array         The converted amount with the associated rate and timestamp
     */
    public function get($from, $to, $amount, $date = null)
    {
        $data = array();

        $data['from'] = $from;
        $data['to'] = $to;
        $data['amount'] = $amount;

        if ($date !== null) {
            $data['date'] = $date;
        }

        $response = $this->fixer->getResponse($this->endpointKey, $data);

        if (!isset($response->result)) {
            throw new \Exception("Error Processing Request", 1);
        }

        return array('timestamp' => $response->info->timestamp, 'rate' => $response->info->rate, 'result' => $response->result);
    }

}
