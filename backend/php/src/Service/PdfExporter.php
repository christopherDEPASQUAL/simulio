<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Simulation;
use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfExporter
{
    public function renderSimulation(Simulation $s): string
    {
        $result = $s->getResultJson();
        $input  = $s->getInputJson();

        $html = $this->buildHtml($s, $input, $result);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // accents

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output(); // bytes
    }

    /** @param array<string,mixed> $input @param array<string,mixed> $result */
    private function buildHtml(Simulation $s, array $input, array $result): string
    {
        $fmt = static fn(float $v): string => number_format($v, 2, ',', ' ');

        // ----- Precompute formatted values (NO function calls in HEREDOC) -----
        $monthFmt  = $fmt((float)($result['monthly_payment_eur'] ?? 0));
        $notaryFmt = $fmt((float)($result['notary_fee_eur'] ?? 0));
        $agencyFmt = $fmt((float)($result['agency_fee_eur'] ?? 0));
        $loanFmt   = $fmt((float)($result['loan_amount_eur'] ?? 0));
        $incomeFmt = $fmt((float)($result['min_monthly_income_eur'] ?? 0));

        $purchaseFmt  = $fmt((float)($input['purchase_price'] ?? 0));
        $downFmt      = $fmt((float)($input['down_payment'] ?? 0));
        $worksFmt     = $fmt((float)($input['works'] ?? 0));
        $yearsVal     = (int)($input['years'] ?? 0);
        $interestVal  = isset($input['interest_rate_percent'])  ? (string)$input['interest_rate_percent']  : '-';
        $insuranceVal = isset($input['insurance_rate_percent']) ? (string)$input['insurance_rate_percent'] : '-';

        $client = $s->getClient();
        $clientStr = $client ? sprintf(
            '%s %s — %s',
            htmlspecialchars($client->getFirstName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($client->getLastName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($client->getEmail(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        ) : 'N/A';

        $generatedAt = $s->getCreatedAt()->format('d/m/Y H:i');
        $simId       = (string)$s->getId();

        return <<<HTML
<!doctype html>
<html lang="fr"><meta charset="utf-8"><style>
  body{font-family: DejaVu Sans, sans-serif; color:#111; font-size:12px}
  h1{font-size:18px;margin:0 0 6px}
  h2{font-size:14px;margin:16px 0 6px}
  table{border-collapse:collapse;width:100%}
  th,td{border:1px solid #ddd;padding:8px;text-align:left}
  .muted{color:#666}
  .box{border:1px solid #e5e7eb;padding:12px;border-radius:8px;margin:8px 0}
</style><body>
  <h1>Simulation crédit — Simulio</h1>
  <div class="muted">Généré: {$generatedAt}</div>

  <div class="box">
    <strong>Client:</strong> {$clientStr}<br/>
    <strong>ID simulation:</strong> {$simId}
  </div>

  <h2>Résultats</h2>
  <table>
    <tr><th>Mensualité</th><td>{$monthFmt} €</td></tr>
    <tr><th>Montant du prêt</th><td>{$loanFmt} €</td></tr>
    <tr><th>Frais de notaire</th><td>{$notaryFmt} €</td></tr>
    <tr><th>Frais d'agence</th><td>{$agencyFmt} €</td></tr>
    <tr><th>Revenu mensuel minimum</th><td>{$incomeFmt} €</td></tr>
  </table>

  <h2>Entrée</h2>
  <table>
    <tr><th>Prix du bien</th><td>{$purchaseFmt} €</td></tr>
    <tr><th>Apport</th><td>{$downFmt} €</td></tr>
    <tr><th>Travaux</th><td>{$worksFmt} €</td></tr>
    <tr><th>Durée</th><td>{$yearsVal} ans</td></tr>
    <tr><th>Taux intérêt</th><td>{$interestVal} %</td></tr>
    <tr><th>Taux assurance</th><td>{$insuranceVal} %</td></tr>
  </table>

  <p class="muted">© Simulio — Document généré automatiquement.</p>
</body></html>
HTML;
    }
}
