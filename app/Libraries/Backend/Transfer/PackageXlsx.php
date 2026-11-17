<?php declare(strict_types = 1);

namespace App\Libraries\Backend\Transfer;

use App\Models\Backend\Components\TransferModel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Genera e protegge i file XLSX del pacchetto Transfer.
 */
class PackageXlsx
{
    private const MANIFEST_FILE = 'manifest.json';

    /**
     * Campi che devono rimanere testuali nel foglio XLSX.
     */

    public function __construct(private readonly TransferModel $model)
    {
    }

    /**
     * Genera il file XLSX completo e il relativo manifest di proprietà.
     *
     * @return array{exportId: string, fileName: string, path: string, recordCount: int}
     */
    public function export(bool $template = false): array
    {
        $owner = $this->getOwnerFingerprint();

        if ($owner === null):
            throw new \RuntimeException('Sessione amministrativa non disponibile.');
        endif;

        TransferArtifacts::cleanup(WRITEPATH . 'exports/' . $this->model->key() . '/', false);
        $exportId = bin2hex(random_bytes(32));
        $directory = $this->getExportDirectory($exportId);
        $fileName = ($template ? $this->model->key() . '_template_' : $this->model->key() . '_') . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = $directory . $fileName;
        $temporaryPath = $filePath . '.tmp';

        if ( ! mkdir($directory, 0750, true) && ! is_dir($directory)):
            throw new \RuntimeException('Impossibile creare la directory di esportazione.');
        endif;

        try
        {
            $package = $template ? $this->model->getPackageTemplate() : $this->model->getPackageData();
            $recordCount = $this->writePackage($package, $temporaryPath);

            if ( ! rename($temporaryPath, $filePath)):
                throw new \RuntimeException('Impossibile finalizzare il file XLSX.');
            endif;

            $checksum = hash_file('sha256', $filePath);

            if ( ! is_string($checksum)):
                throw new \RuntimeException('Impossibile verificare il file XLSX.');
            endif;

            $manifest = [
                'exportId' => $exportId, 'owner' => $owner, 'fileName' => $fileName,
                'recordCount' => $recordCount, 'createdAt' => date(DATE_ATOM), 'expiresAt' => time() + 3600, 'checksum' => $checksum,
            ];
            $manifestJson = json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

            if (file_put_contents($directory . self::MANIFEST_FILE, $manifestJson, LOCK_EX) !== strlen($manifestJson)):
                throw new \RuntimeException('Impossibile salvare il manifest di esportazione.');
            endif;

            return ['exportId' => $exportId, 'fileName' => $fileName, 'path' => $filePath, 'recordCount' => $recordCount];
        }
        catch (\Throwable $e)
        {
            $this->removeExportDirectory($directory);
            throw $e;
        }
    }

    /**
     * Scrive il pacchetto già congelato usando gli stessi tipi e la stessa formattazione del template.
     * Il percorso viene costruito esclusivamente dai servizi server-side del modulo.
     */
    public function writePackage(array $package, string $path): int
    {
        $spreadsheet = new Spreadsheet();
        $recordCount = 0;

        try
        {
            $spreadsheet->removeSheetByIndex(0);
            $spreadsheet->getProperties()
                ->setCreator('Essential')
                ->setTitle($this->model->message('titles.index'))
                ->setSubject($this->model->key() . ' XLSX package');

            foreach ($package as $table => $data):
                $worksheet = $spreadsheet->createSheet();
                $worksheet->setTitle($table);

                foreach ($data['headers'] as $columnIndex => $header):
                    $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . '1';
                    $worksheet->setCellValueExplicit($coordinate, $header, DataType::TYPE_STRING);
                endforeach;

                foreach ($data['rows'] as $rowIndex => $row):
                    foreach ($data['headers'] as $columnIndex => $field):
                        $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 2);
                        $this->setCellValue($worksheet, $coordinate, $field, $row[$field] ?? null);
                    endforeach;
                endforeach;

                $recordCount += count($data['rows']);
                $lastColumn = Coordinate::stringFromColumnIndex(count($data['headers']));
                $lastRow = max(1, count($data['rows']) + 1);

                $worksheet->freezePane('A2');
                $worksheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $worksheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF34495E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1F2D3A']]],
                ]);

                foreach (range(1, count($data['headers'])) as $columnIndex):
                    $worksheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setAutoSize(true);
                endforeach;
            endforeach;

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($path);

            return $recordCount;
        }
        finally
        {
            $spreadsheet->disconnectWorksheets();
        }
    }

    /**
     * Restituisce il file soltanto se appartiene alla sessione amministrativa corrente.
     *
     * @param string $exportId Identificatore crittografico dell'esportazione
     * @return array{path: string, fileName: string}|null
     */
    public function getDownloadFile(string $exportId): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $exportId) !== 1):
            return null;
        endif;

        $directory = $this->getExportDirectory($exportId);
        $manifestPath = $directory . self::MANIFEST_FILE;

        if ( ! is_file($manifestPath)):
            return null;
        endif;

        try
        {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\Throwable)
        {
            return null;
        }

        if ( ! is_array($manifest)):
            return null;
        endif;

        $owner = $this->getOwnerFingerprint();

        if ($owner === null || ! isset($manifest['owner']) || ! hash_equals((string) $manifest['owner'], $owner)
            || ! is_int($manifest['expiresAt'] ?? null) || $manifest['expiresAt'] < time()):
            return null;
        endif;

        $fileName = basename((string) ($manifest['fileName'] ?? ''));
        $filePath = $directory . $fileName;

        if ($fileName === '' || ! is_file($filePath)):
            return null;
        endif;

        $checksum = hash_file('sha256', $filePath);

        if ( ! is_string($checksum) || ! isset($manifest['checksum']) || ! hash_equals((string) $manifest['checksum'], $checksum)):
            return null;
        endif;

        return ['path' => $filePath, 'fileName' => $fileName];
    }

    /**
     * Scrive un valore preservandone il tipo applicativo.
     */
    private function setCellValue(object $worksheet, string $coordinate, string $field, mixed $value): void
    {
        if ($value === null):
            $worksheet->setCellValue($coordinate, null);
            return;
        endif;

        if (in_array($field, $this->model->textFields(), true)):
            $worksheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
            return;
        endif;

        $worksheet->setCellValueExplicit($coordinate, (int) $value, DataType::TYPE_NUMERIC);
    }

    /**
     * Restituisce il fingerprint della sessione proprietaria del file.
     */
    private function getOwnerFingerprint(): ?string
    {
        return service('authorization')->currentAccessFingerprint();
    }

    /**
     * Restituisce la directory fisica associata all'esportazione.
     */
    private function getExportDirectory(string $exportId): string
    {
        return WRITEPATH . ('exports/' . $this->model->key() . '/') . $exportId . DIRECTORY_SEPARATOR;
    }

    /**
     * Rimuove esclusivamente i file creati nella directory della sessione fallita.
     */
    private function removeExportDirectory(string $directory): void
    {
        $files = glob($directory . '*.xlsx*');
        $files = is_array($files) ? $files : [];
        $files[] = $directory . self::MANIFEST_FILE;

        foreach ($files as $file):
            if (is_file($file)):
                unlink($file);
            endif;
        endforeach;

        if (is_dir($directory)):
            rmdir($directory);
        endif;
    }
}
