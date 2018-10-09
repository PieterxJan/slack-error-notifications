<?php
/**
 * Slack error notifications plugin for Craft CMS 3.x
 *
 * Plugin used to log error in your Craft website to a channel on Slack
 *
 * @link      https://github.com/pieterxjan
 * @copyright Copyright (c) 2018 Pieter-Jan Claeysens
 */

namespace pieterxjan\slackerrornotifications\services;

use Craft;
use craft\models\Info;
use craft\base\Component;
use craft\events\ExceptionEvent;
use pieterxjan\slackerrornotifications\SlackErrorNotifications;

/**
 * SlackService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Pieter-Jan Claeysens
 * @package   SlackErrorNotifications
 * @since     0.0.1
 */
class SlackService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     SlackErrorNotifications::$plugin->slackService->exampleService()
     *
     * @return mixed
     */
    public function sendNotification(\Exception $exception)
    {
        $name = $_SERVER['HTTP_HOST'];
        $user = \Yii::$app->user->identity;
        $webhook = SlackErrorNotifications::$plugin->getSettings()->webhook;

        $json = [
            'pretext' => ':warning: There has been an error in *' . $name . '*',
            "color" => "#ff0000",
            "fields" => [
                [
                    "title" => "Error",
                    "short" => false,
                    "value" => $exception->getMessage(),
                ], [
                    "title" => "File",
                    "short" => false,
                    "value" => $exception->getFile(),
                ], [
                    "title" => "Rule",
                    "short" => true,
                    "value" => $exception->getLine(),
                ], [
                    "title" => "Page",
                    "short" => true,
                    "value" => $_SERVER['REQUEST_URI'],
                ], [
                    "title" => "IP",
                    "short" => true,
                    "value" => $_SERVER['REMOTE_ADDR'],
                ], [
                    "title" => "User",
                    "short" => true,
                    "value" => ($user && $user->username) ? $user->username : '-',
                ], [
                    "title" => "User Agent",
                    "short" => false,
                    "value" => $_SERVER['HTTP_USER_AGENT'],
                ]
            ]
        ];

        $query = http_build_query(['payload' => json_encode($json)]);
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $webhook);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}
