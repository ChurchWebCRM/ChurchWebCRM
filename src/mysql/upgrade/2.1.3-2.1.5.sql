SET @upgradeStartTime = NOW();

INSERT IGNORE INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_type`, `cfg_default`, `cfg_tooltip`, `cfg_section`, `cfg_category`,cfg_order, `cfg_value`) VALUES
(1036, 'sQuickBooksDepositSlipParameters', 'textarea', '{jsonarray}', 'QuickBooks Deposit Slip Coordinates', 'ChurchInfoReport', "Step7",19,'{"date1":{"x":"12","y":"35+7"},"leftx":"10","topy":"10","amountoffset":"35","lineItemInterval":{"x":"45","y":"15"},"numberOfItems":{"x":"54","y":"89"},"subtotal":{"x":"90","y":"90"},"toptotal":{"x":"90","y":"100"},"title":{"x":"200","y":"200"},"date2":{"x":"250","y":"200"}}'),
(1037, 'sPeachtreeDepositSlipParameters', 'textarea', '{jsonarray}', 'Peachtree Deposit Slip Coordinates', 'ChurchInfoReport', "Step7",20,''),
(1038, 'sGenericDepositSlipParameters', 'textarea', '{jsonarray}', 'Generic Deposit Slip Coordinates', 'ChurchInfoReport', "Step7",21,'{"depositSlipFrontColumns":"135","date1":{"x":"15","y":"27"},"date2":{"x":"15","y":"5"},"customerName1":{"x":"32","y":"37"},"cash":{"x":"135","y":"32"},"checks":{"x":"135","y":"39"},"leftx":"10","topy":"10","amountoffset":"35","lineItemInterval":{"x":"45","y":"15"},"numberOfItems":{"x":"54","y":"89"},"subtotal":{"x":"90","y":"90"},"toptotal":{"x":"90","y":"100"},"title":{"x":"200","y":"200"},"accountNumber":{"x":"125","y":"15"}}');

INSERT IGNORE INTO version_ver (ver_version, ver_update_start, ver_update_end) VALUES ('2.1.5',@upgradeStartTime,NOW());
