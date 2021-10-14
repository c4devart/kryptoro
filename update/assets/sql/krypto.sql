CREATE TABLE `block_exp_address_list_krypto` (
  `id_block_exp_address_list` int(11) NOT NULL,
  `symbol__block_exp_address_list` varchar(50) NOT NULL,
  `address__block_exp_address_list` text NOT NULL,
  `nb_confirm__block_exp_address_list` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `block_exp_tx_krypto` (
  `id_block_exp_tx` int(11) NOT NULL,
  `symbol_block_exp_tx` varchar(50) NOT NULL,
  `tx_block_exp_tx` text NOT NULL,
  `status_block_exp_tx` int(11) NOT NULL DEFAULT '0',
  `confirmations_block_exp_tx` int(11) NOT NULL,
  `data_block_exp_tx` text NOT NULL,
  `date_block_exp_tx` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `exchanges_withdraw_krypto` (
  `id_exchanges_withdraw` int(11) NOT NULL,
  `symbol_exchanges_withdraw` varchar(50) NOT NULL,
  `exchange_exchanges_withdraw` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `internal_order_krypto` ADD `type_internal_order` VARCHAR(50) NOT NULL DEFAULT 'market' AFTER `ref_internal_order`, ADD `status_internal_order` INT NOT NULL DEFAULT '1' AFTER `type_internal_order`, ADD `ordered_price_internal_order` VARCHAR(50) NOT NULL DEFAULT '0' AFTER `status_internal_order`;

ALTER TABLE `block_exp_address_list_krypto`
  ADD PRIMARY KEY (`id_block_exp_address_list`);

ALTER TABLE `block_exp_tx_krypto`
  ADD PRIMARY KEY (`id_block_exp_tx`);

ALTER TABLE `exchanges_withdraw_krypto`
  ADD PRIMARY KEY (`id_exchanges_withdraw`);

ALTER TABLE `block_exp_address_list_krypto`
  MODIFY `id_block_exp_address_list` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `block_exp_tx_krypto`
  MODIFY `id_block_exp_tx` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

COMMIT;
