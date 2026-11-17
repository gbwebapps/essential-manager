<?php declare(strict_types = 1);

namespace App\Libraries\Backend\Transfer;

/** Segnala un esito del commit non ricostruibile in sicurezza dal solo staging. */
class TransferRecoveryException extends \RuntimeException
{
}
