<?php declare(strict_types = 1);

namespace App\Libraries\Backend;

/** Provides the standard backend assets for the message list and detail. */
class MessagesClass
{
    /**
     * Loads the date-range picker and its localization for the inbox.
     *
     * @return list<array{id: string, path: string, position: string, target: string}> Scripts for the message list.
     */
    public function getJsShowAll(): array
    {
        $language = service('request')->getLocale();
        $locale = $language === 'en' ? 'default' : $language;

        return [
            ['id' => 'flatpickr-js', 'path' => 'assets/vendor/flatpickr/js/flatpickr.min.js', 'position' => 'before', 'target' => 'messages-js'],
            ['id' => $locale . '-js', 'path' => 'assets/vendor/flatpickr/js/' . $locale . '.js', 'position' => 'after', 'target' => 'flatpickr-js'],
        ];
    }

    /**
     * Loads the date-range picker stylesheet for the inbox.
     *
     * @return list<array{id: string, path: string, position: string, target: string}> Stylesheets for the message list.
     */
    public function getCssShowAll(): array
    {
        return [
            ['id' => 'flatpickr-css', 'path' => 'assets/vendor/flatpickr/css/flatpickr.min.css', 'position' => 'before', 'target' => 'backend-css'],
        ];
    }

    /**
     * Loads the PDF exporter for the message detail.
     *
     * @return list<array{id: string, path: string, position: string, target: string}> Scripts for the message detail.
     */
    public function getJsShow(): array
    {
        if ( ! AuthorizationClass::allows(service('authorization')->currentAdmin(), 'messages_export')):
            return [];
        endif;
        return [
            ['id' => 'html2pdf-js', 'path' => 'assets/vendor/html2pdf/js/html2pdf.bundle.min.js', 'position' => 'before', 'target' => 'messages-js'],
        ];
    }
}
