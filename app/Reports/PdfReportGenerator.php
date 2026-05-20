<?php
namespace App\Reports;

// Solo el paso 'format' varía. Los otros 4 pasos son idénticos.
// Target de Template Method en S4 L.
class PdfReportGenerator
{
    public function generate(array $params): string
    {
        // Paso 1: validar (duplicado en Csv y Excel)
        if (empty($params['from']) || empty($params['to'])) {
            throw new \InvalidArgumentException('Date range required.');
        }

        // Paso 2: consultar datos (duplicado)
        $orders = \App\Models\Order::whereBetween('created_at', [$params['from'], $params['to']])
            ->with(['customer.user', 'vendor', 'items'])
            ->get();

        // Paso 3: formatear (ÚNICO paso que varía)
        $content = $this->formatAsPdf($orders);

        // Paso 4: persistir (duplicado)
        $filename = 'report_' . now()->format('Ymd_His') . '.pdf';
        $path = storage_path("app/reports/{$filename}");
        file_put_contents($path, $content);

        // Paso 5: notificar (duplicado)
        \App\Support\Logger::getInstance()->log("PDF report generated: {$filename}");

        return $path;
    }

    private function formatAsPdf($orders): string
    {
        // Stub: retorna contenido dummy
        return "%PDF-1.4 Report with " . $orders->count() . " orders";
    }
}
