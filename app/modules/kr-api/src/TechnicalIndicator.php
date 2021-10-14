<?php

class ADX{

	static function lag($period = 14){
		return ($period * 2) - 1;
	}

	static function run($data, $period = 14){

		$true_range_array = array();
		$plus_dm_array = array();
		$minus_dm_array = array();
		$dx_array = array();
		$previous_adx = null;

		//loop data
		foreach($data as $key => $row){

			//need 2 data points
			if($key > 0){


				//calc true range
				$true_range = max(($data[$key]['high'] - $data[$key]['low']), abs($data[$key]['high'] - $data[$key - 1]['close']) , abs($data[$key]['low'] - $data[$key - 1]['close']));

				//calc +DM 1
				$plus_dm_1 = (($data[$key]['high'] - $data[$key - 1]['high']) > ($data[$key - 1]['low'] - $data[$key]['low'])) ? max($data[$key]['high'] - $data[$key-1]['high'], 0) : 0;

				//calc -DM 1
				$minus_dm_1 = (($data[$key - 1]['low'] - $data[$key]['low']) > ($data[$key]['high'] - $data[$key-1]['high'])) ? max($data[$key - 1]['low'] - $data[$key]['low'], 0) : 0;

				//add to front
				array_unshift($true_range_array, $true_range);
				array_unshift($plus_dm_array, $plus_dm_1);
				array_unshift($minus_dm_array, $minus_dm_1);

				//pop back if too long
				if(count($true_range_array) > $period)
					array_pop($true_range_array);

				if(count($plus_dm_array) > $period)
					array_pop($plus_dm_array);

				if(count($minus_dm_array) > $period)
					array_pop($minus_dm_array);
			}


			//calc dx
			if(count($true_range_array) == $period){

				$sum_true_range = array_reduce($true_range_array, function($result, $item) {
					    $result += $item;
					    return $result;
					}, 0);

        if($sum_true_range == 0) continue;


				$sum_plus_dm = array_reduce($plus_dm_array, function($result, $item) {
					    $result += $item;
					    return $result;
					}, 0);

				$sum_minus_dm = array_reduce($minus_dm_array, function($result, $item) {
					    $result += $item;
					    return $result;
					}, 0);


				$plus_di = ($sum_plus_dm / $sum_true_range) * 100;
				$minus_di = ($sum_minus_dm / $sum_true_range) * 100;


				$di_diff = abs($plus_di - $minus_di);
				$di_sum = $plus_di + $minus_di;

        if($di_sum == 0) continue;

				$dx = ($di_diff / $di_sum) * 100;

				//add to front
				array_unshift($dx_array, $dx);
				//pop back if too long
				if(count($dx_array) > $period)
					array_pop($dx_array);

			}

			//calc first adx
			if(count($dx_array) == $period){

				$sum = array_reduce($dx_array, function($result, $item) {
					    $result += $item;
					    return $result;
					}, 0);

				$adx = $sum / $period;

				//save
				$data[$key]['val'] = [
					'adx' => $adx,
					'mdi' => $minus_di,
					'pdi' => $plus_di
				];
				$previous_adx = $adx;
			}


			//calc further adx
			if(isset($previous_adx)){
				$adx = (($previous_adx * ($period - 1)) + $dx) / $period;

				//save
				$data[$key]['val'] =  [
					'adx' => $adx,
					'mdi' => $minus_di,
					'pdi' => $plus_di
				];
				$previous_adx = $adx;
			}

		}

		return $data;
	}
}

class RSI{
	static function lag($period = 14){
		return $period;
	}

	static function run($data, $period = 14){
		$change_array = array();
		//loop data
		foreach($data as $key => $row){
			//need 2 points to get change
			if($key >= 1){
				$change = $data[$key]['close'] - $data[$key - 1]['close'];
				//add to front
				array_unshift($change_array, $change);
				//pop back if too long
				if(count($change_array) > $period)
					array_pop($change_array);
			}
			//have enough data to calc rsi
			if($key > $period){
				//reduce change array getting sum loss and sum gains
				$res = array_reduce($change_array, function($result, $item) {
							if($item >= 0)
								$result['sum_gain'] += $item;

							if($item < 0)
								$result['sum_loss'] += abs($item);
					  		return $result;
						}, array('sum_gain' => 0, 'sum_loss' => 0));
				$avg_gain = $res['sum_gain'] / $period;
				$avg_loss = $res['sum_loss'] / $period;
				//check divide by zero
				if($avg_loss == 0){
					$rsi = 100;
				} else {
					//calc and normalize
					$rs = $avg_gain / $avg_loss;
					$rsi = 100 - (100 / ( 1 + $rs));
				}
				//save
				$data[$key]['val'] = $rsi;

			}
		}
		return $data;
	}
}

class ATR{
	static function lag($period = 14){
		return $period - 1;
	}

	static function run($data, $period = 14){

		//init
		$High_minus_Low  = null;
		$High_minus_Close_past = null;
		$Low_minus_Close_past = null;
		$TR = null;
		$TR_sum = 0;
		//loop data
		foreach($data as $key => $row){
			$High_minus_Low = $data[$key]['high'] - $data[$key]['low'];
			if($key >= 1){
				$High_minus_Close_past = abs($data[$key]['high'] - $data[$key - 1]['close']);
				$Low_minus_Close_past = abs($data[$key]['low'] - $data[$key - 1]['close']);
			}

			if(isset($High_minus_Close_past) && isset($Low_minus_Close_past)){
				$TR = max($High_minus_Low, $High_minus_Close_past, $Low_minus_Close_past);
				//sum first TRs for first ATR avg
				if ($key <= $period)
					$TR_sum += $TR;
			}
			//first ATR
			if ($key == $period){
				$atr = $TR_sum / $period;
				$data[$key]['val'] = $atr;
				$previous_ATR = $atr;
			}
			//remaining ATR
			if($key > $period){
				$atr = (($previous_ATR * ($period - 1)) + $TR) / $period;
				$data[$key]['val'] = $atr;
				$previous_ATR = $atr;
			}
		}
		return $data;
	}
}

class EMA{
	static function lag($period = 5){
		return $period - 1;
	}
	static function run($data, $period = 5){
	 	$smoothing_constant = 2 / ($period + 1);
	 	$previous_EMA = null;

		//loop data
		foreach($data as $key => $row){

			//skip init rows
			if ($key >= $period){
				//first
				if(!isset($previous_EMA)){
					$sum = 0;
					for ($i = $key - ($period-1); $i <= $key; $i ++)
						$sum += $data[$i]['close'];
					//calc sma
					$sma = $sum / $period;
					//save
					$data[$key]['val'] = $sma;
					$previous_EMA = $sma;
				}else{
					//ema formula
 					$ema = ($data[$key]['close'] - $previous_EMA) * $smoothing_constant + $previous_EMA;
 					//save
			 		$data[$key]['val'] = $ema;
			 		$previous_EMA = $ema;
				}
			}
		}
		return $data;
    }
}

?>
