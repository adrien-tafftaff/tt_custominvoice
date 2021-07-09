<?php
/**
 * @category    Module / customizations
 * @author      Adrien THIERRY www.tafftaff.fr
 * @copyright   2021 Adrien THIERRY
 * @version     1.0
 * @link        https://www.tafftaff.fr
 * @since       File available since Release 1.0
*/
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

header('Location: ../');
exit;
