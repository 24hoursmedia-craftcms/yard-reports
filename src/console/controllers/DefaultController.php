<?php
/**
 * Yard Reports plugin for Craft CMS 3.x
 *
 * Export sprout forms to CSV (Excel supported)
 *
 * @link      https://en.24hoursmedia.com
 * @copyright Copyright (c) 2020 24HOURSMEDIA
 */

namespace twentyfourhoursmedia\yardreports\console\controllers;

use twentyfourhoursmedia\yardreports\YardReports;

use Craft;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Default Command
 *
 * @author    24HOURSMEDIA
 * @package   YardReports
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Handle yard-reports/default console commands
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'something';

        echo "Welcome to the console DefaultController actionIndex() method\n";

        return $result;
    }

    /**
     * Handle yard-reports/default/do-something console commands
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console DefaultController actionDoSomething() method\n";

        return $result;
    }
}
