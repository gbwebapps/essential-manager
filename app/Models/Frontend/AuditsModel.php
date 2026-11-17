<?php declare(strict_types = 1);

namespace App\Models\Frontend;


/**
 * Registra le attivita dei clienti nel registro separato users_audits.
 */
class AuditsModel extends FrontendModel
{
    /**
     * Registra un evento dell'area utente separatamente dagli audit amministrativi.
     *
     * @param string $action Codice dell'operazione da registrare.
     * @param string $section Sezione cui appartiene l'evento.
     * @param string $details Descrizione dell'evento.
     * @param object|null $identity Cliente associato all'evento, oppure ospite.
     * @return bool
     */
    public function logActivity(string $action, string $section, string $details, ?object $identity = null): bool
    {
        $request = \Config\Services::request();

        $userUuid = $identity ? ($identity->uuid ?: null) : null;
        $username = $identity ? ($identity->email ?: 'Ospite') : 'Ospite';
        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();
        $createdAt = date('Y-m-d H:i:s');

        /* Scriviamo la query SQL nativa utilizzando i segnaposto ? */
        $sql = "insert into `users_audits` (`user_uuid`, `username`, `action`, `section`, `details`, `ip_address`, `user_agent`, `created_at`) values (?, ?, ?, ?, ?, ?, ?, ?)";

        /* Eseguiamo la query passando i parametri nell'array di binding */
        return $this->db->query($sql, [$userUuid, $username, $action, $section, $details, $ipAddress, $userAgent, $createdAt]);
    }
}
