<?php declare(strict_types = 1);

namespace Config\Frontend;

use CodeIgniter\Config\BaseConfig;

/**
 * Impostazioni di base del sito pubblico, indipendenti dall'interfaccia amministrativa.
 */
class General extends BaseConfig
{
    /** Nome visualizzato nelle intestazioni delle pagine pubbliche e del profilo. */
    public string $siteName = 'Essential';

    /** Titoli della home per lingua; una traduzione vuota ripiega su en. */
    public array $title = ['en' => 'Essential'];

    /** Descrizioni della home per lingua; una traduzione vuota ripiega su en. */
    public array $description = ['en' => 'Centralized management, simple and secure for your administrative activities.'];

    /** Lingua usata quando mancano una scelta del visitatore e una lingua browser supportata. */
    public string $language = 'en';

    /** Fuso usato soltanto nella presentazione delle date del frontend. */
    public string $timezone = 'Europe/Rome';
}
