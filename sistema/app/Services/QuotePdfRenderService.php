<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvalidArgumentException;

final class QuotePdfRenderService
{
    public function renderHtmlToPdf(string $html): string
    {
        if (trim($html) === '') {
            throw new InvalidArgumentException('El HTML para renderizar PDF no puede estar vacío.');
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
