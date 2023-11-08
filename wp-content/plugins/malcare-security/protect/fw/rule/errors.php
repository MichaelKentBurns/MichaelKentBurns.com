<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('MCProtectRuleError')) :
class MCProtectRuleError extends Exception {
//Root rule error class.
}
endif;
