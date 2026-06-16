<?php

namespace App\Filament\Pages;

use App\Jobs\ImportProductsJob;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportarProductos extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Importar productos';
    protected static ?string $navigationGroup = 'Catálogo';
    protected static ?int    $navigationSort  = 99;
    protected static ?string $title           = 'Importar productos desde Excel';
    protected static string  $view            = 'filament.pages.importar-productos';

    public ?array $data             = [];
    public string  $step            = 'upload'; // upload | preview | importing | done
    public array   $previewProducts = [];
    public array   $previewVariants = [];
    public array   $headerErrors    = [];
    public ?string $importId        = null;
    public array   $result          = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('Archivo Excel (.xlsx)')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/octet-stream',
                    ])
                    ->maxSize(20480)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function cargarPreview(): void
    {
        $state    = $this->form->getState();
        $filePath = $state['file'] ?? null;

        if (!$filePath) {
            Notification::make()->title('Selecciona un archivo primero')->danger()->send();
            return;
        }

        $this->headerErrors    = [];
        $this->previewProducts = [];
        $this->previewVariants = [];

        try {
            $fullPath    = Storage::disk('local')->path($filePath);
            $spreadsheet = IOFactory::load($fullPath);

            foreach (['Productos', 'Variantes'] as $sheetName) {
                if ($spreadsheet->getSheetByName($sheetName) === null) {
                    $this->headerErrors[] = "Falta la hoja '{$sheetName}' en el archivo.";
                }
            }

            if (empty($this->headerErrors)) {
                $productosSheet  = $spreadsheet->getSheetByName('Productos');
                $productosHdrs   = $this->getHeaders($productosSheet);
                foreach (['sku', 'nombre', 'precio', 'tiene_variantes', 'estado'] as $col) {
                    if (!in_array($col, $productosHdrs)) {
                        $this->headerErrors[] = "Falta la columna '{$col}' en la hoja Productos.";
                    }
                }

                $variantesSheet = $spreadsheet->getSheetByName('Variantes');
                $variantesHdrs  = $this->getHeaders($variantesSheet);
                foreach (['sku_producto', 'sku_variante', 'stock'] as $col) {
                    if (!in_array($col, $variantesHdrs)) {
                        $this->headerErrors[] = "Falta la columna '{$col}' en la hoja Variantes.";
                    }
                }
            }

            if (empty($this->headerErrors)) {
                $this->previewProducts = $this->getPreviewRows($spreadsheet->getSheetByName('Productos'), 3, 5);
                $this->previewVariants = $this->getPreviewRows($spreadsheet->getSheetByName('Variantes'), 3, 5);
            }

            $this->step = 'preview';
        } catch (\Throwable $e) {
            Notification::make()->title('Error al leer el archivo')->body($e->getMessage())->danger()->send();
        }
    }

    public function importar(): void
    {
        $state    = $this->form->getState();
        $filePath = $state['file'] ?? null;

        if (!$filePath) {
            Notification::make()->title('Archivo no disponible')->danger()->send();
            return;
        }

        $this->importId = (string) Str::uuid();
        Cache::put("import:{$this->importId}", ['status' => 'processing', 'step' => 'Iniciando...'], now()->addHours(2));

        ImportProductsJob::dispatch($filePath, $this->importId);
        $this->step = 'importing';
    }

    public function checkImportStatus(): void
    {
        if (!$this->importId) return;

        $data = Cache::get("import:{$this->importId}");
        if ($data && in_array($data['status'], ['done', 'failed'])) {
            $this->result = $data;
            $this->step   = 'done';
        }
    }

    public function reiniciar(): void
    {
        $this->step            = 'upload';
        $this->data            = [];
        $this->previewProducts = [];
        $this->previewVariants = [];
        $this->headerErrors    = [];
        $this->importId        = null;
        $this->result          = [];
        $this->form->fill();
    }

    // -------------------------------------------------------------------------

    private function getHeaders(Worksheet $sheet): array
    {
        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $v = $cell->getValue();
                if ($v !== null && $v !== '') $headers[] = (string) $v;
            }
        }
        return $headers;
    }

    private function getPreviewRows(Worksheet $sheet, int $startRow, int $limit): array
    {
        $headers = $this->getHeaders($sheet);
        $rows    = [];
        $count   = 0;

        foreach ($sheet->getRowIterator($startRow) as $row) {
            if ($count >= $limit) break;
            $rowData  = [];
            $hasValue = false;
            $i        = 0;
            $cellIter = $row->getCellIterator();
            $cellIter->setIterateOnlyExistingCells(false);
            foreach ($cellIter as $cell) {
                if ($i >= count($headers)) break;
                $v              = $cell->getValue();
                $rowData[$headers[$i]] = $v;
                if ($v !== null && $v !== '') $hasValue = true;
                $i++;
            }
            if ($hasValue) {
                $rows[] = $rowData;
                $count++;
            }
        }

        return $rows;
    }
}
