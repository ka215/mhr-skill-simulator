<?php
/**
 * MHRise Skill Simulator Customer Endpoint
 * 
 * @package   MHRSS
 * @since     1.0.0
 * @author    Ka2
 * @copyright 2021 ka2.org
 * @license   MIT
 */
require_once( __DIR__ . '/vendor/autoload.php' );

use MHRise\SkillSimulator\CustomerCore;

$class = 'MHRise\SkillSimulator\CustomerCore';

if ( class_exists( $class ) ) {
    CustomerCore::get_object();
} else {
    trigger_error( "Unable to load class: $class", E_USER_WARNING );
    exit;
}
