<?php

return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'product` DROP COLUMN `hscode`');
