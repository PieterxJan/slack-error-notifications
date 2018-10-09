<?php

namespace pieterxjan\slackerrornotifications\models;

use craft\base\Model;

class Settings extends Model
{
    public $webhook = '';

    public function rules()
    {
        return [
            [['webhook'], 'required'],
        ];
    }
}