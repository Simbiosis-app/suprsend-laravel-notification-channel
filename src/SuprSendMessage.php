<?php

namespace NotificationChannels\SuprSend;

use NotificationChannels\SuprSend\Enums\NotificationCategory;

class SuprSendMessage
{
    protected string $workflowName;
    protected string $template;
    protected NotificationCategory $notificationCategory;
    protected array $data = [];
    protected array $users = [];
    protected string $delay;
    protected array $attachments = [];

    public function __construct()
    {
        $this->notificationCategory = NotificationCategory::Transactional;
    }

    public static function create(): self
    {
        return new self();
    }

    public function workflowName(string $workflowName): self
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function template(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function notificationCategory(NotificationCategory $notificationCategory): self
    {
        $this->notificationCategory = $notificationCategory;

        return $this;
    }

    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function users(array $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function attachments(array $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function delay(string $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    public function toArray(): array
    {
        $this->data['$attachments'] = $this->attachments;

        return [
            'name' => $this->workflowName,
            'template' => $this->template,
            'notification_category' => $this->notificationCategory,
            'delay' => $this->delay ?? '',
            'users' => $this->users,
            'data' => $this->data,
        ];
    }
}
