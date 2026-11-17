<?php declare(strict_types = 1);

namespace App\Models\Frontend;

use App\Models\BaseModel;

/**
 * Base dei model frontend per whitelist, confronto dati e gestione delle immagini utente.
 */
abstract class FrontendModel extends BaseModel
{
	protected array $toCompare = [];
	/**
	 * Inizializza il model e le configurazioni utilizzate dalle sue operazioni.
	 *
	 * @return void
	 */
	protected function initModel(): void
	{
		parent::initModel();
		helper(['settings', 'interface', 'user_audits']);
	}

	/**
	 * Filtra il payload secondo la whitelist dell'operazione.
	 *
	 * @param array $posts Payload dell'operazione sottoposto alla whitelist.
	 * @param array $allowedFields Nomi dei campi consentiti per l'operazione.
	 * @return array
	 */
	protected function checkAllowedFields(array $posts, array $allowedFields): array
	{
	    foreach (array_keys($posts) as $key):
	        /* Rimuove il campo se non è presente nei campi consentiti */
	        if ( ! in_array($key, $allowedFields, true)):
	            unset($posts[$key]);
	        endif;
	    endforeach;

	    return $posts;
	}

	/**
	 * Confronta i campi consentiti e gli allegati con i dati originali.
	 *
	 * @param array $posts Payload dell'operazione sottoposto alla whitelist.
	 * @param object $original Dati originali da confrontare.
	 * @return bool
	 */
	protected function hasDataChanged(array $posts, object $original): bool
	{
	    /* 1. Controllo dei campi nativi della tabella (Valido per TUTTI i moduli) */
        foreach ($this->toCompare as $field):
            
            /* Se il campo è presente nel POST, normalizziamo il confronto a stringa */
            if (isset($posts[$field])):
                
                /* Recuperiamo il valore originale gestendo il possibile NULL dal DB */
                $originalValue = $original->$field ?? '';

                /* Il cast a (string) azzera la differenza tra null e "" senza toccare i dati reali */
                if ((string)$posts[$field] !== (string)$originalValue):
                    return true;
                endif;

            endif;
        endforeach;

	    /* 2. Controllo dei file caricati (Valido per TUTTI i moduli che accettano allegati) */
	    foreach (['images'] as $type):
	        if (isset($posts[$type]) && is_array($posts[$type])):
	            foreach ($posts[$type] as $file):
	                if ($file instanceof \CodeIgniter\HTTP\Files\UploadedFile && $file->isValid() && ! $file->hasMoved()):
	                    return true;
	                endif;
	            endforeach;
	        endif;
	    endforeach;

	    return false;
	}
	/**
	 * Collega le immagini salvate alla loro entita e assegna la copertina iniziale.
	 *
	 * @param array $filenames Nomi dei file gia salvati dal servizio di upload.
	 * @param string $uuid UUID del cliente o del proprietario delle immagini.
	 * @param string $entity Entita proprietaria delle immagini.
	 * @param string $action Codice dell'operazione da registrare.
	 * @return bool
	 */
	protected function insertImages(array $filenames, string $uuid, string $entity, string $action = 'add'): bool
	{
		if ($filenames === []):
			return false;
		endif;

	    $dataImage = [];
	    $flag = false;

	    if ($action === 'edit'):
	        $sql = "select 1 from images where entity_uuid = ? and is_cover = ? and entity = ? limit 1";
	        $result = $this->db->query($sql, [$uuid, '1', $entity])->getRow();
	        $flag = $result ? true : false;
	    endif;

	    foreach ($filenames as $k => $v):
	        $dataImage[$k]['entity'] = $entity;
	        $dataImage[$k]['entity_uuid'] = $uuid;
	        $dataImage[$k]['filename'] = $v;
	        
	        if ($flag):
	            $dataImage[$k]['is_cover'] = '0';
	        else:
	            $dataImage[$k]['is_cover'] = ($k === 0) ? '1' : '0';
	        endif;
	    endforeach;

	    $placeholders = [];
	    $bind = [];
	    $now = date('Y-m-d H:i:s');

	    foreach ($dataImage as $row):
	        $placeholders[] = "(?, ?, ?, ?, ?)";
	        $bind[] = $row['entity'];
	        $bind[] = $row['entity_uuid'];
	        $bind[] = $row['filename'];
	        $bind[] = $row['is_cover'];
	        $bind[] = $now;
	    endforeach;

	    $sql = "insert into images (entity, entity_uuid, filename, is_cover, created_at) values " . implode(", ", $placeholders);
	    $result = $this->db->query($sql, $bind);

	    return $result !== false && $this->db->affectedRows() === count($dataImage);
	}

    /**
     * Fornisce il servizio condiviso per elaborare e salvare le immagini.
     *
     * @return \App\Libraries\Backend\UploadClass
     */
    protected function getUploadService()
    {
        return new \App\Libraries\Backend\UploadClass();
    }

	/**
	 * Genera un UUID versione 4 con dati casuali crittograficamente sicuri.
	 *
	 * @return string
	 */
	protected function generateUUID(): string
	{
	    $data = random_bytes(16);

	    $data[6] = chr((ord($data[6]) & 0x0f) | 0b01000000);
	    $data[8] = chr((ord($data[8]) & 0x3f) | 0b10000000);

	    $hex = bin2hex($data);

	    return vsprintf('%08s-%04s-%04s-%04s-%12s', sscanf($hex, '%8s%4s%4s%4s%12s'));
	}
}
