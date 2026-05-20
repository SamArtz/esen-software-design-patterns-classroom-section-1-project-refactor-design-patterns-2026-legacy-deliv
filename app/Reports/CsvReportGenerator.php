<?php
namespace App\Reports;

// Solo el paso 'format' varía. Los otros 4 pasos son idénticos.
class CsvReportGenerator
{
    public function generate(array $params): string
    {
        // Paso 1: validar (duplicado en Pdf y Excel)
        if (empty($params['from']) || empty($params['to'])) {
            throw new \InvalidArgumentException('Date range required.');
        }

        // Paso 2: consultar datos (duplicado)
        $orders = \App\Models\Order::whereBetween('created_at', [$params['from'], $params['to']])
            ->with(['customer.user', 'vendor', 'items'])
            ->get();

        // Paso 3: formatear (ÚNICO paso que varía)
        $content = $this->formatAsCsv($orders);

        // Paso 4: persistir (duplicado)
        $filename = 'report_' . now()->format('Ymd_His') . '.csv';
        $path = storage_path("app/reports/{$filename}");
        file_put_contents($path, $content);

        // Paso 5: notificar (duplicado)
        \App\Support\Logger::getInstance()->log("CSV report generated: {$filename}");

        return $path;
    }

    private function formatAsCsv($orders): string
    {
        $lines = ["id,customer,vendor,total,status"];
        foreach ($orders as $order) {
            $lines[] = implode(',', [
                $order->id,
                $order->customer->user->name ?? '',
                $order->vendor->business_name ?? '',
                $order->total,
                $order->status,
            ]);
        }
        return implode("\n", $lines);
    }
}
