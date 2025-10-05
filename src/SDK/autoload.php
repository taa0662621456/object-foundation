<?php
use ObjectFoundation\SDK\FoundationSDK;
if (!defined('OBJECT_FOUNDATION_SDK_BOOTED')) {
    define('OBJECT_FOUNDATION_SDK_BOOTED', true);
    FoundationSDK::autoDetect()->boot();
}