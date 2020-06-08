<?php
/**
 * Yard Reports plugin for Craft CMS 3.x
 *
 * Export sprout forms to CSV (Excel supported)
 *
 * @link      https://en.24hoursmedia.com
 * @copyright Copyright (c) 2020 24HOURSMEDIA
 */

namespace twentyfourhoursmedia\yardreports\assetbundles\yardreports;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    24HOURSMEDIA
 * @package   YardReports
 * @since     1.0.0
 */
class YardReportsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@twentyfourhoursmedia/yardreports/assetbundles/yardreports/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/YardReports.js',
            'js/YardReportFieldIndex.js',
        ];

        $this->css = [
            'css/YardReports.css',
        ];

        parent::init();
    }
}
