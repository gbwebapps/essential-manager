<?php declare(strict_types = 1);

namespace Config\Frontend;

use CodeIgniter\Config\BaseConfig;

/** Configures the public contact form and its optional notification. */
class Contacts extends BaseConfig
{
    /** @var bool Whether the public contact form accepts requests. */
    public bool $enabled = false;

    /** @var string Destination for new-message notifications. An empty value disables email. */
    public string $notificationRecipient = '';

    /**
     * Reads the settings for the current installation.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->enabled = (bool) env('contacts.frontend.enabled', false);
        $this->notificationRecipient = trim((string) env('contacts.notificationRecipient', $this->notificationRecipient));
    }
}
