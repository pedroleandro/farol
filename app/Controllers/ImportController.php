<?php

namespace App\Controllers;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\LogEvent;
use App\Core\Message;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use JetBrains\PhpStorm\NoReturn;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportController extends Controller
{
    private const SHEET_NAME = "DADOS";

    private const HEADER_MAP = [
        "CLIENTE" => "client_name",
        "CIDADE" => "client_city",
        "ESTADO" => "client_state",
        "TIPO DE FRETE" => "freight_type_raw",
        "TIPO RODADO" => "vehicle_type",
        "QNT DE PRODUTOS" => "product_qty",
        "QNT ITENS NOTA" => "item_qty",
        "VALOR DO FRETE" => "freight_value",
        "NF" => "invoice_number",
        "DATA DO PEDIDO" => "order_date",
        "PRODUZIDA" => "produced_flag",
        "DATA DO CARREGAMENTO" => "loading_date",
        "DATA DA ENTREGA" => "delivery_date",
        "DATA PREVISAO ENTREGA" => "expected_delivery",
    ];

    private const FREIGHT_TYPE_MAP = [
        "FROTA PROPRIA" => Order::FREIGHT_OWN_FLEET,
        "CIF" => Order::FREIGHT_CIF_CARRIER,
        "FOB" => Order::FREIGHT_FOB_CLIENT,
    ];

    public function __construct()
    {
        parent::__construct("App");
        $this->requireRole(User::ROLES_CAN_IMPORT);
    }

    #[NoReturn]
    public function index(): void
    {
        echo $this->render("import/index", [
            "title" => "Importar Planilha | " . APP_NAME,
        ]);

        clear_old();
    }

    #[NoReturn]
    public function store(?array $data): void
    {
        $this->validateCsrfToken($data ?? [], "/importar");

        if (empty($_FILES["spreadsheet"]) || $_FILES["spreadsheet"]["error"] !== UPLOAD_ERR_OK) {
            Message::error("Selecione um arquivo de planilha válido para importar.");
            redirect("/importar");
            return;
        }

        try {
            $spreadsheet = IOFactory::load($_FILES["spreadsheet"]["tmp_name"]);
        } catch (\Throwable $e) {
            Message::error("Não foi possível ler o arquivo enviado. Verifique se é um .xlsx válido.");
            redirect("/importar");
            return;
        }

        if (!$spreadsheet->sheetNameExists(self::SHEET_NAME)) {
            Message::error("A planilha enviada não contém uma aba chamada \"" . self::SHEET_NAME . "\".");
            redirect("/importar");
            return;
        }

        $sheet = $spreadsheet->getSheetByName(self::SHEET_NAME);
        $columnMap = $this->buildColumnMap($sheet);
        $highestRow = $sheet->getHighestDataRow();

        $results = [];
        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;

        $userId = Auth::user()->id ?? null;

        for ($row = 2; $row <= $highestRow; $row++) {
            $orderNumberRaw = $this->cellValue($sheet, 1, $row);
            $clientName = trim((string)$this->cellValue($sheet, $columnMap["client_name"] ?? 0, $row));

            if ($orderNumberRaw === null && $clientName === "") {
                continue;
            }

            $rowResult = [
                "line" => $row,
                "order_number" => null,
                "client_name" => $clientName ?: "—",
                "outcome" => null,
                "warnings" => [],
            ];

            $orderNumber = $this->normalizeOrderNumber($orderNumberRaw);
            $rowResult["order_number"] = $orderNumber ?? "—";

            if (!$orderNumber) {
                $rowResult["outcome"] = "failed";
                $rowResult["reason"] = "Número do pedido ausente ou inválido.";
                $errorCount++;
                $results[] = $rowResult;
                continue;
            }

            if ($clientName === "") {
                $rowResult["outcome"] = "failed";
                $rowResult["reason"] = "Nome do cliente ausente.";
                $errorCount++;
                $results[] = $rowResult;
                continue;
            }

            $clientCity = trim((string)$this->cellValue($sheet, $columnMap["client_city"] ?? 0, $row)) ?: null;
            $clientState = trim((string)$this->cellValue($sheet, $columnMap["client_state"] ?? 0, $row)) ?: null;

            $client = Client::findByNameAndCity($clientName, $clientCity);

            if (!$client) {
                $client = new Client();
                $client->fill([
                    "name" => $clientName,
                    "city" => $clientCity,
                    "state" => $clientState ? strtoupper($clientState) : null,
                ]);
                $client->save();
                $rowResult["warnings"][] = "Cliente cadastrado automaticamente.";
            }

            $freightTypeRaw = $this->normalizeText(
                (string)$this->cellValue($sheet, $columnMap["freight_type_raw"] ?? 0, $row)
            );
            $freightType = self::FREIGHT_TYPE_MAP[$freightTypeRaw] ?? null;
            if ($freightTypeRaw !== "" && $freightType === null) {
                $rowResult["warnings"][] = "Tipo de frete não reconhecido: \"{$freightTypeRaw}\".";
            }

            $productQty = $this->cellInt($sheet, $columnMap["product_qty"] ?? 0, $row);
            $itemQty = $this->cellInt($sheet, $columnMap["item_qty"] ?? 0, $row);
            $freightValue = $this->cellFloat($sheet, $columnMap["freight_value"] ?? 0, $row);
            $invoiceNumber = trim((string)$this->cellValue($sheet, $columnMap["invoice_number"] ?? 0, $row)) ?: null;
            $vehicleType = trim((string)$this->cellValue($sheet, $columnMap["vehicle_type"] ?? 0, $row)) ?: null;

            $orderDate = $this->cellDate($sheet, $columnMap["order_date"] ?? 0, $row);
            $loadingDate = $this->cellDate($sheet, $columnMap["loading_date"] ?? 0, $row);
            $deliveryDate = $this->cellDate($sheet, $columnMap["delivery_date"] ?? 0, $row);
            $expectedDelivery = $this->cellDate($sheet, $columnMap["expected_delivery"] ?? 0, $row);

            $producedFlag = $this->normalizeText(
                (string)$this->cellValue($sheet, $columnMap["produced_flag"] ?? 0, $row)
            );
            if ($producedFlag === "NAO" && ($loadingDate || $deliveryDate)) {
                $rowResult["warnings"][] = "Planilha indica \"não produzido\", mas há data de carregamento/entrega preenchida.";
            }

            $fields = [
                "client_id" => $client->getId(),
                "product_qty" => $productQty,
                "item_qty" => $itemQty,
                "invoice_number" => $invoiceNumber,
                "order_date" => $orderDate,
                "freight_type" => $freightType,
                "vehicle_type" => $vehicleType,
                "freight_value" => $freightValue,
                "loading_date" => $loadingDate,
                "delivery_date" => $deliveryDate,
                "expected_delivery" => $expectedDelivery,
                "source" => Order::SOURCE_SPREADSHEET,
            ];

            $existingOrder = (new Order())->where("order_number", "=", $orderNumber)->first();

            if ($existingOrder) {
                $existingOrder->fill($fields);
                $existingOrder->save();

                $rowResult["outcome"] = "updated";
                $updatedCount++;
            } else {
                $inferredStatus = $this->inferStatus($loadingDate, $deliveryDate);

                $fields["order_number"] = $orderNumber;
                $fields["tracking_code"] = Order::generateTrackingCode();
                $fields["status"] = $inferredStatus;
                $fields["is_pending"] = 0;

                $newOrder = new Order();
                $newOrder->fill($fields);
                $newOrder->save();

                OrderStatusHistory::logTransition($newOrder->getId(), $userId, null, $inferredStatus);

                $rowResult["outcome"] = "created";
                $createdCount++;
            }

            $results[] = $rowResult;
        }

        $totalRows = count($results);

        $this->recordImport($userId, $_FILES["spreadsheet"]["name"], $totalRows, $createdCount, $updatedCount, $errorCount);

        AuditLog::record(LogEvent::IMPORT_COMPLETED, $userId, [
            "file_name" => $_FILES["spreadsheet"]["name"],
            "total_rows" => $totalRows,
            "created_count" => $createdCount,
            "updated_count" => $updatedCount,
            "error_count" => $errorCount,
        ]);

        echo $this->render("import/report", [
            "title" => "Resultado da Importação | " . APP_NAME,
            "results" => $results,
            "totalRows" => $totalRows,
            "createdCount" => $createdCount,
            "updatedCount" => $updatedCount,
            "errorCount" => $errorCount,
            "fileName" => $_FILES["spreadsheet"]["name"],
        ]);
    }

    private function buildColumnMap(Worksheet $sheet): array
    {
        $map = ["client_name" => 2];
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($col = 1; $col <= $highestColumn; $col++) {
            $headerRaw = (string)$this->cellValue($sheet, $col, 1);
            $normalized = $this->normalizeText($headerRaw);

            foreach (self::HEADER_MAP as $headerText => $fieldName) {
                if ($normalized === $headerText) {
                    $map[$fieldName] = $col;
                    break;
                }
            }
        }

        return $map;
    }

    private function normalizeText(string $text): string
    {
        $accents = ["Á" => "A", "À" => "A", "Ã" => "A", "Â" => "A", "É" => "E", "Ê" => "E",
            "Í" => "I", "Ó" => "O", "Ô" => "O", "Õ" => "O", "Ú" => "U", "Ç" => "C"];

        $text = strtr(strtoupper(trim($text)), $accents);

        return preg_replace('/\s+/', ' ', $text);
    }

    private function normalizeOrderNumber(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            return (string)(int)$raw;
        }

        return trim((string)$raw) ?: null;
    }

    private function inferStatus(?string $loadingDate, ?string $deliveryDate): string
    {
        if ($deliveryDate) {
            return Order::STATUS_DELIVERED;
        }

        if ($loadingDate) {
            return Order::STATUS_IN_TRANSIT;
        }

        return Order::STATUS_IN_PRODUCTION;
    }

    private function recordImport(
        ?int $userId,
        string $fileName,
        int $totalRows,
        int $createdCount,
        int $updatedCount,
        int $errorCount
    ): void {
        $connection = \App\Core\Connection::getInstance();

        $statement = $connection->prepare(
            "INSERT INTO imports (user_id, file_name, total_rows, created_count, updated_count, error_count, created_at)
             VALUES (:user_id, :file_name, :total_rows, :created_count, :updated_count, :error_count, NOW())"
        );

        $statement->execute([
            "user_id" => $userId,
            "file_name" => $fileName,
            "total_rows" => $totalRows,
            "created_count" => $createdCount,
            "updated_count" => $updatedCount,
            "error_count" => $errorCount,
        ]);
    }

    private function cellValue(Worksheet $sheet, int $col, int $row): mixed
    {
        if ($col < 1) {
            return null;
        }

        $coordinate = Coordinate::stringFromColumnIndex($col) . $row;
        return $sheet->getCell($coordinate)->getValue();
    }

    private function cellInt(Worksheet $sheet, int $col, int $row): ?int
    {
        $value = $this->cellValue($sheet, $col, $row);
        return ($value !== null && $value !== '' && is_numeric($value)) ? (int)$value : null;
    }

    private function cellFloat(Worksheet $sheet, int $col, int $row): ?float
    {
        $value = $this->cellValue($sheet, $col, $row);
        return ($value !== null && $value !== '' && is_numeric($value)) ? (float)$value : null;
    }

    private function cellDate(Worksheet $sheet, int $col, int $row): ?string
    {
        if ($col < 1) {
            return null;
        }

        $coordinate = Coordinate::stringFromColumnIndex($col) . $row;
        $cell = $sheet->getCell($coordinate);
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (ExcelDate::isDateTime($cell) && is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float)$value)->format("Y-m-d");
        }

        $timestamp = strtotime((string)$value);
        return $timestamp ? date("Y-m-d", $timestamp) : null;
    }
}