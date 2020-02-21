<?php
namespace importantcoding\multipleorderpdf\base;

use importantcoding\multipleorderpdf\MultipleOrderPdf;
use importantcoding\multipleorderpdf\services\MySms as MySmsService;
use Craft;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static $plugin;


    // Public Methods
    // =========================================================================

    public function getMySms()
    {
        return $this->get('mySms');
    }

    private function _setPluginComponents()
    {
        $this->setComponents([
            'mySms' => MySmsService::class,
        ]);
    }

}