<?php

namespace NotificationChannels\SuprSend\Enums;

enum NotificationCategory: string
{
    case Transactional = 'transactional';
    case Promotional = 'promotional';
    case System = 'system';
}