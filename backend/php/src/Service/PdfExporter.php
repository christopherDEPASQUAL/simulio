<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Simulation;
use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfExporter
{
    public function make(Simulation $simulation): string
    {
        // Données
        $input  = (array) $simulation->getInputJson();
        $result = (array) $simulation->getResultJson();
        $client = $simulation->getClient();

        // Helpers
        $eur = fn($v) => number_format((float)($v ?? 0), 2, ',', ' ') . ' €';
        $n0  = fn($v) => number_format((float)($v ?? 0), 0, ',', ' ') . ' €';
        $id  = $simulation->getId();

        // Date (Europe/Paris)
        $tz = new \DateTimeZone('Europe/Paris');
        $createdAt = $simulation->getCreatedAt();
        $createdStr = $createdAt
            ? $createdAt->setTimezone($tz)->format('d/m/Y H:i')
            : (new \DateTimeImmutable('now', $tz))->format('d/m/Y H:i');

        // Champs Résultats
        $monthly    = $eur($result['monthly_payment_eur']    ?? null);
        $loanAmount = $eur($result['loan_amount_eur']        ?? null);
        $notaryFee  = $eur($result['notary_fee_eur']         ?? null);
        $agencyFee  = $eur($result['agency_fee_eur']         ?? null);
        $minIncome  = $eur($result['min_monthly_income_eur'] ?? null);

        // Champs Entrée
        $price   = $n0($input['purchase_price'] ?? null);
        $down    = $n0($input['down_payment']   ?? null);
        $works   = $n0($input['works']          ?? null);
        $years   = (int)($input['years'] ?? 0);
        $rate    = isset($input['interest_rate_percent'])  ? (float)$input['interest_rate_percent']  : null;
        $insur   = isset($input['insurance_rate_percent']) ? (float)$input['insurance_rate_percent'] : null;

        $rateStr  = $rate  !== null ? rtrim(rtrim(number_format($rate, 2, ',', ' '), '0'), ',') . ' %' : '—';
        $insurStr = $insur !== null ? rtrim(rtrim(number_format($insur,2, ',', ' '), '0'), ',') . ' %' : '—';

        // Client
        $clientEmail = $client?->getEmail() ?: ($input['client']['email'] ?? '—');
        $clientName  = trim(($input['client']['first_name'] ?? '') . ' ' . ($input['client']['last_name'] ?? ''));
        if ($clientName === '') $clientName = '—';

        // HTML (layout proche de ta capture)
        $html = <<<HTML
<!doctype html>
<html lang="fr"><head>
  <meta charset="utf-8">
  <title>Simulation crédit — Simulio</title>
  <style>
    @page { margin: 28px; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 6px; }
    .muted { color: #666; }
    .section { margin-top: 18px; }
    .box {
      border: 1px solid #ddd; border-radius: 6px; padding: 10px; background: #fafafa;
      margin: 8px 0 14px;
    }
    .label { font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f5f5f5; text-align: left; }
    .w50 { width: 50%; }
    .right { text-align: right; }
    .footer { margin-top: 20px; color: #666; font-size: 10px; }
  </style>
</head>
<body>

  <h1>Simulation crédit — Simulio</h1>
  <div class="muted">Généré: {$createdStr}</div>

  <div class="box">
    <div><span class="label">Client:</span> {$clientName} — {$clientEmail}</div>
    <div><span class="label">ID simulation:</span> {$id}</div>
  </div>

  <div class="section">
    <div class="label" style="margin-bottom:6px;">Résultats</div>
    <table>
      <tbody>
        <tr><th class="w50">Mensualité</th><td class="right">{$monthly}</td></tr>
        <tr><th>Montant du prêt</th><td class="right">{$loanAmount}</td></tr>
        <tr><th>Frais de notaire</th><td class="right">{$notaryFee}</td></tr>
        <tr><th>Frais d'agence</th><td class="right">{$agencyFee}</td></tr>
        <tr><th>Revenu mensuel minimum</th><td class="right">{$minIncome}</td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="label" style="margin-bottom:6px;">Entrée</div>
    <table>
      <tbody>
        <tr><th class="w50">Prix du bien</th><td class="right">{$price}</td></tr>
        <tr><th>Apport</th><td class="right">{$down}</td></tr>
        <tr><th>Travaux</th><td class="right">{$works}</td></tr>
        <tr><th>Durée</th><td class="right">{$years} ans</td></tr>
        <tr><th>Taux intérêt</th><td class="right">{$rateStr}</td></tr>
        <tr><th>Taux assurance</th><td class="right">{$insurStr}</td></tr>
      </tbody>
    </table>
  </div>

  <div class="footer">© Simulio — Document généré automatiquement.</div>

</body></html>
HTML;

        // Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Pagination bas de page
        $canvas  = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font("DejaVu Sans", "normal");
        $canvas->page_text($canvas->get_width()-120, $canvas->get_height()-28, "Page {PAGE_NUM}/{PAGE_COUNT}", $font, 9, [0.42,0.42,0.42]);

        return $dompdf->output();
    }
}
