<?php

namespace NotificationChannels\SuprSend\Exceptions;

class CouldNotSendNotification extends \Exception
{
    public static function serviceRespondedWithAnError($response)
    {
        return new static($response->getMessage());
    }
}
