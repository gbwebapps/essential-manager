<?php declare(strict_types = 1);

namespace Config\Frontend;

use CodeIgniter\Config\BaseConfig;

/** Configures the public information pages for the application. */
class Pages extends BaseConfig
{
    /**
     * Enables the public information pages.
     *
     * @var bool
     */
    public bool $enabled = false;

    /**
     * Maps English URL slugs to views, translation keys, and optional menu entries.
     *
     * @var array<string, array{view: string, title: string, description: string, menu?: bool}>
     */
    public array $items = [
        'about-us' => [
            'view' => 'frontend/pages/aboutView',
            'title' => 'frontend/pages/examples.about.title',
            'description' => 'frontend/pages/examples.about.description',
            'menu' => true,
        ],
        'faq' => [
            'view' => 'frontend/pages/faqView',
            'title' => 'frontend/pages/examples.faq.title',
            'description' => 'frontend/pages/examples.faq.description',
            'menu' => true,
        ],
    ];

    /**
     * Loads the feature flag and validates the configured pages.
     *
     * The optional PagesLocal.php file replaces the default list with application-specific pages.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->enabled = (bool) env('pages.enabled', $this->enabled);
        if ( ! $this->enabled):
            return;
        endif;

        $path = APPPATH . 'Config/Frontend/PagesLocal.php';
        if (is_file($path)):
            $local = require $path;
            $this->items = is_array($local) ? $local : [];
        endif;

        foreach ($this->items as $slug => $page):
            if (
                ! is_string($slug)
                || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)
                || in_array($slug, ['backend', 'auth', 'account', 'interface'], true)
                || ! is_string($page['view'] ?? null)
                || ! preg_match('~^frontend/pages/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+$~D', $page['view'])
                || ! is_file(APPPATH . 'Views/' . $page['view'] . '.php')
                || ! is_string($page['title'] ?? null)
                || ! is_string($page['description'] ?? null)
            ):
                unset($this->items[$slug]);
            endif;
        endforeach;
    }
}
