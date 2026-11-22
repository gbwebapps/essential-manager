<?php declare(strict_types = 1);

namespace Config\Backend;

use CodeIgniter\Config\BaseConfig;

/** Attivazione delle sezioni opzionali del backend, indipendente dal frontend e dai ruoli. */
class Features extends BaseConfig
{
    /** Abilita la gestione amministrativa degli utenti. */
    public bool $usersEnabled = false;

    /** Abilita la consultazione e gestione amministrativa dei messaggi ricevuti. */
    public bool $contactsEnabled = false;

    /**
     * Legge esclusivamente i flag backend dell'installazione corrente.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->usersEnabled = (bool) env('users.backend.enabled', false);
        $this->contactsEnabled = (bool) env('contacts.backend.enabled', false);
    }

    /**
     * Indica se la sezione può essere esposta da menu e contatori.
     * @param string $section Identificatore del controller o della sezione.
     * @return bool False per Users o Messaggi disabilitati; le altre sezioni restano indipendenti.
     */
    public function isSectionEnabled(string $section): bool
    {
        return match (strtolower($section)) {
            'users' => $this->usersEnabled,
            'messages' => $this->contactsEnabled,
            default => true,
        };
    }

    /**
     * Applica il flag della sezione prima dei permessi, anche per superadmin e permesso all.
     * @param string|null $permission Nome del permesso amministrativo.
     * @return bool True se la configurazione consente di valutare il permesso.
     */
    public function isPermissionEnabled(?string $permission): bool
    {
        return $permission === null || $this->isSectionEnabled(explode('_', $permission, 2)[0]);
    }

    /**
     * Protegge i componenti condivisi, comprese esportazioni e gallerie degli utenti.
     * @param string $entity Nome dell'entità o della tabella ricevuto dal componente.
     * @return bool True se l'entità non appartiene a una sezione disabilitata.
     */
    public function isEntityEnabled(string $entity): bool
    {
        $entity = strtolower($entity);
        if ($entity === 'users' || str_starts_with($entity, 'users_')):
            return $this->usersEnabled;
        endif;
        return $entity === 'contact_messages' ? $this->contactsEnabled : $this->isSectionEnabled($entity);
    }
}
