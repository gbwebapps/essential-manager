<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use App\Filters\Frontend\LanguageFilter;
use App\Libraries\Frontend\SiteSettings;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Frontend\Pages;

/**
 * Registra la preferenza linguistica del visitatore senza intervenire sulla sessione amministrativa.
 */
class InterfaceController extends FrontendController
{
    /**
     * Salva la lingua in un cookie locale e restituisce i testi della pagina corrente.
     *
     * @return ResponseInterface
     */
    public function changeLanguage(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return $this->response->setStatusCode(400);
        endif;

        $language = $this->request->getPost('language');
        if ( ! is_string($language) || ! in_array($language, config('App')->supportedLocales, true)):
            return $this->jsonResponse(['result' => false, 'message' => lang('frontend/interface.messages.languageError')]);
        endif;

        $rawKeys = $this->request->getPost('keys');
        $keys = is_string($rawKeys) ? json_decode($rawKeys, true) : [];
        if ( ! is_array($keys) || ! array_is_list($keys) || count($keys) > 250):
            return $this->jsonResponse(['result' => false, 'message' => lang('frontend/interface.messages.languageError')]);
        endif;
        $translations = $this->translateKeys($keys, $language);
        $site = new SiteSettings();

        $path = rtrim((string) parse_url(config('App')->baseURL, PHP_URL_PATH), '/') . '/';
        $this->response->setHeader('Cache-Control', 'no-store');
        $this->response->setCookie([
            'name' => LanguageFilter::COOKIE_NAME,
            'value' => $language,
            'expire' => 31536000,
            'path' => $path,
            'prefix' => '',
            'secure' => $this->request->isSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $this->jsonResponse([
            'result' => true,
            'language' => $language,
            'translations' => (object) $translations,
            'siteTitle' => $site->title($language),
            'siteDescription' => $site->description($language),
        ]);
    }

    /**
     * Resolves translation keys from the public frontend catalogs and shared interface catalogs.
     *
     * @param array $keys Translation keys requested by the browser.
     * @param string $locale Validated language code.
     * @return array<string, string> Translations indexed by key.
     */
    private function translateKeys(array $keys, string $locale): array
    {
        $translations = [];
        $pagesEnabled = config(Pages::class)->enabled;
        foreach ($keys as $key):
            if ( ! is_string($key) || isset($translations[$key])):
                continue;
            endif;

            $existingKey = preg_match('~^(?:frontend/(?:account|auth|contacts|home|interface)|backend/components/(?:galleryOne|uploadPreview)|backend/global)\.[a-zA-Z0-9_.]{1,120}$~D', $key);
            $pageKey = $pagesEnabled && preg_match('~^frontend/pages/[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*\.[A-Za-z0-9_.]{1,120}$~D', $key);
            if ( ! $existingKey && ! $pageKey):
                continue;
            endif;

            $value = lang($key, [], $locale);
            if (is_string($value) && $value !== $key):
                $translations[$key] = $value;
            endif;
        endforeach;

        return $translations;
    }
}
