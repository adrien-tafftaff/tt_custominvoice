<?php

return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'product` ADD `hscode` INT NULL DEFAULT 0');
