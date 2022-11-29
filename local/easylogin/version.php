<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2022160200;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2019111200;        // Requires this Moodle version
$plugin->component = 'local_easylogin';         // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array (
	"local_pinion"=>2022020300
);
