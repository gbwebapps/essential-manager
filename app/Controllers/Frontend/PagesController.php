<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Frontend\Pages;

/** Renders the public information pages registered by the application. */
class PagesController extends FrontendController
{
    /**
     * Renders a registered page with its content and metadata.
     *
     * @param string $slug URL slug registered for the page.
     * @return string Rendered HTML page.
     * @throws PageNotFoundException If the page is disabled or not registered.
     */
    public function show(string $slug): string
    {
        $config = config(Pages::class);
        if ( ! $config->enabled || ! isset($config->items[$slug])):
            throw PageNotFoundException::forPageNotFound();
        endif;

        return $this->render('frontend/pages/pageView', [
            'controller' => 'pages',
            'action' => $slug,
            'page' => $config->items[$slug],
        ]);
    }
}
