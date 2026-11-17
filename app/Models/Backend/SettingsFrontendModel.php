<?php declare(strict_types = 1);

namespace App\Models\Backend;

/**
 * Valida le impostazioni del sito pubblico e usa la persistenza comune senza scrivere configurazioni backend.
 */
class SettingsFrontendModel extends SettingsModel
{
    protected string $settingsLanguage = 'backend/settingsFrontend';
    protected string $settingsAuditSection = 'settingsFrontend';

    /**
     * Limita i payload ai cinque campi del form.
     *
     * @param array $posts Dati ricevuti dal form amministrativo.
     * @return array|null Payload filtrato oppure null se contiene tipi incompatibili.
     */
    public function prepareGeneralPosts(array $posts): ?array
    {
        $prepared = [];
        foreach (['siteName', 'language', 'timezone'] as $key):
            if ( ! isset($posts[$key]) || ! is_string($posts[$key])):
                return null;
            endif;
            $prepared[$key] = trim($posts[$key]);
        endforeach;

        foreach (['title', 'description'] as $key):
            if ( ! isset($posts[$key]) || ! is_array($posts[$key])
                || array_diff(array_keys($posts[$key]), config('App')->supportedLocales) !== []):
                return null;
            endif;
            $prepared[$key] = [];
            foreach (config('App')->supportedLocales as $locale):
                $value = $posts[$key][$locale] ?? '';
                if ( ! is_string($value)):
                    return null;
                endif;
                $prepared[$key][$locale] = trim($value);
            endforeach;
        endforeach;

        return $prepared;
    }

    /**
     * Richiede una base inglese e ammette traduzioni vuote, che il sito risolvera in lettura.
     *
     * @return array Regole CI4 per il form completo.
     */
    public function generalSettingsValidateRules(): array
    {
        $rules = [
            'siteName' => ['label' => lang('backend/settingsFrontend.labels.siteName'), 'rules' => ['required', 'min_length[2]', 'max_length[100]', 'safeText']],
            'language' => ['label' => lang('backend/settingsFrontend.labels.language'), 'rules' => ['required', 'in_list[' . implode(',', config('App')->supportedLocales) . ']']],
            'timezone' => ['label' => lang('backend/settingsFrontend.labels.timezone'), 'rules' => ['required', 'timezone']],
        ];

        foreach (config('App')->supportedLocales as $locale):
            $rules['title.' . $locale] = ['label' => lang('backend/settingsFrontend.labels.title') . ' (' . $locale . ')', 'rules' => [$locale === 'en' ? 'required' : 'permit_empty', 'max_length[255]', 'safeText']];
            $rules['description.' . $locale] = ['label' => lang('backend/settingsFrontend.labels.description') . ' (' . $locale . ')', 'rules' => [$locale === 'en' ? 'required' : 'permit_empty', 'max_length[1000]', 'safeText']];
        endforeach;

        return $rules;
    }

    /**
     * Ammette soltanto la configurazione generale frontend; i namespace backend rimangono inaccessibili.
     *
     * @param string $namespace Classe di configurazione autorizzata dal controller.
     * @return array<string>
     */
    protected function allowedSettingsFields(string $namespace): array
    {
        return $namespace === 'Frontend\General' ? ['siteName', 'title', 'description', 'language', 'timezone'] : [];
    }

    /**
     * Controlla se almeno una delle impostazioni ancora disponibili è salvata nel database.
     *
     * @param string $namespace Classe di configurazione richiesta.
     * @return bool
     */
    public function hasDatabaseSettings(string $namespace): bool
    {
        $fields = $this->allowedSettingsFields($namespace);
        return $fields !== [] && $this->db->table('settings')->where('class', $namespace)
            ->whereIn('key', $fields)->countAllResults() > 0;
    }
}
