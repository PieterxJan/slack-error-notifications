<?php
/**
 * Slack error notifications plugin for Craft CMS 3.x
 *
 * Plugin used to log error in your Craft website to a channel on Slack
 *
 * @link      https://github.com/pieterxjan
 * @copyright Copyright (c) 2018 Pieter-Jan Claeysens
 */

namespace pieterxjan\slackerrornotifications;

use Craft;
use yii\base\Event;
use craft\base\Plugin;
use craft\web\ErrorHandler;
use craft\events\ExceptionEvent;
use pieterxjan\slackerrornotifications\models\Settings;


/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Pieter-Jan Claeysens
 * @package   SlackErrorNotifications
 * @since     0.0.1
 *
 * @property  SlackServiceService $slackService
 */
class SlackErrorNotifications extends Plugin
{
    public $hasCpSettings = true;

    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * SlackErrorNotifications::$plugin
     *
     * @var SlackErrorNotifications
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.0.1';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * SlackErrorNotifications::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Do something after we're installed
        Event::on(
            ErrorHandler::class,
            ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
            function (ExceptionEvent $event) {
                $message = $event->exception->getMessage();
                $file = $event->exception->getFile();
                $line = $event->exception->getLine();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $remote_ip = $_SERVER['REMOTE_ADDR'];
                $page = $_SERVER['REQUEST_URI'];

                $webhook = $this->getSettings()->webhook;

                // if (\Craft::$app->config->getGeneral()->devMode === true) {
                //     return;
                // }

                self::$plugin->slackService->sendNotification($event->exception);
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'slack-error-notifications',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // ========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'slack-error-notifications/settings', [
                'settings' => $this->getSettings()
            ]
        );
    }
}