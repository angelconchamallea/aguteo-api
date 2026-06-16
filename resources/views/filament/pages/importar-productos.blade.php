<x-filament-panels::page>

    {{-- ═══════════════════════════ PASO 1: SUBIR ═══════════════════════════ --}}
    @if ($step === 'upload')
        <x-filament::section>
            <x-slot name="heading">Subir archivo</x-slot>
            <x-slot name="description">
                Usa la plantilla oficial. El archivo debe tener las hojas <strong>Productos</strong>
                y <strong>Variantes</strong> con las columnas correctas.
            </x-slot>

            <form wire:submit="cargarPreview">
                {{ $this->form }}
                <div class="mt-6">
                    <x-filament::button type="submit" icon="heroicon-o-eye">
                        Previsualizar
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    {{-- ══════════════════════════ PASO 2: PREVIEW ══════════════════════════ --}}
    @elseif ($step === 'preview')

        @if (!empty($headerErrors))
            <x-filament::section>
                <x-slot name="heading">Errores en el archivo</x-slot>
                <div class="space-y-2">
                    @foreach ($headerErrors as $error)
                        <div class="flex items-start gap-2 text-sm text-red-600 dark:text-red-400">
                            <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <x-filament::button wire:click="reiniciar" color="gray" icon="heroicon-o-arrow-left">
                        Volver y corregir
                    </x-filament::button>
                </div>
            </x-filament::section>

        @else
            {{-- Preview Productos --}}
            <x-filament::section>
                <x-slot name="heading">Vista previa — Productos (primeras 5 filas de datos)</x-slot>
                @if (!empty($previewProducts))
                    <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-800">
                                    @foreach (array_keys($previewProducts[0]) as $col)
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $col }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($previewProducts as $row)
                                    <tr class="even:bg-gray-50 dark:even:bg-gray-900/50">
                                        @foreach ($row as $value)
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 text-gray-600 dark:text-gray-400 max-w-[160px] truncate">
                                                {{ $value ?? '—' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No se encontraron filas de datos en la hoja Productos.</p>
                @endif
            </x-filament::section>

            {{-- Preview Variantes --}}
            <x-filament::section>
                <x-slot name="heading">Vista previa — Variantes (primeras 5 filas de datos)</x-slot>
                @if (!empty($previewVariants))
                    <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-800">
                                    @foreach (array_keys($previewVariants[0]) as $col)
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $col }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($previewVariants as $row)
                                    <tr class="even:bg-gray-50 dark:even:bg-gray-900/50">
                                        @foreach ($row as $value)
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 text-gray-600 dark:text-gray-400 max-w-[160px] truncate">
                                                {{ $value ?? '—' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No se encontraron filas en la hoja Variantes.</p>
                @endif
            </x-filament::section>

            <div class="flex flex-wrap gap-3">
                <x-filament::button wire:click="importar" icon="heroicon-o-arrow-up-tray">
                    Importar ahora
                </x-filament::button>
                <x-filament::button wire:click="reiniciar" color="gray" icon="heroicon-o-arrow-left">
                    Cambiar archivo
                </x-filament::button>
            </div>
        @endif

    {{-- ═════════════════════════ PASO 3: IMPORTANDO ════════════════════════ --}}
    @elseif ($step === 'importing')
        <x-filament::section>
            <x-slot name="heading">Importando en segundo plano…</x-slot>
            <div wire:poll.3s="checkImportStatus">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-primary-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $result['step'] ?? 'Procesando filas en lotes de 50…' }}
                    </p>
                </div>
                <p class="mt-2 text-xs text-gray-400">Esta página se actualiza sola cada 3 segundos.</p>
            </div>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">¿El proceso tarda demasiado?</x-slot>
            <p class="text-sm text-gray-500">
                Asegúrate de que el worker de colas esté corriendo:
            </p>
            <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-800 rounded p-3 overflow-x-auto">docker compose exec laravel.test php artisan queue:work --tries=1</pre>
        </x-filament::section>

    {{-- ══════════════════════════ PASO 4: RESULTADO ═════════════════════════ --}}
    @elseif ($step === 'done')
        <x-filament::section>
            @if (($result['status'] ?? '') === 'failed')
                <x-slot name="heading">Importación fallida</x-slot>
                <p class="text-sm text-red-600 dark:text-red-400">{{ $result['message'] ?? 'Error desconocido' }}</p>
            @else
                <x-slot name="heading">Importación completada</x-slot>

                {{-- Tarjetas de resumen --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    @foreach ([
                        ['Productos creados',      $result['products']['created'] ?? 0, 'green'],
                        ['Productos actualizados', $result['products']['updated'] ?? 0, 'blue'],
                        ['Variantes creadas',      $result['variants']['created'] ?? 0, 'green'],
                        ['Variantes actualizadas', $result['variants']['updated'] ?? 0, 'blue'],
                    ] as [$label, $count, $color])
                        <div class="rounded-xl border border-{{ $color }}-200 dark:border-{{ $color }}-800 bg-{{ $color }}-50 dark:bg-{{ $color }}-950 p-4 text-center">
                            <p class="text-3xl font-bold text-{{ $color }}-700 dark:text-{{ $color }}-400">{{ $count }}</p>
                            <p class="mt-1 text-xs text-{{ $color }}-600 dark:text-{{ $color }}-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Tabla de errores --}}
                @if (!empty($result['errors']))
                    <div class="mt-2">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            Filas con error ({{ count($result['errors']) }})
                        </h3>
                        <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-800">
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">Hoja</th>
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">Fila</th>
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">Campo</th>
                                        <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result['errors'] as $err)
                                        <tr class="even:bg-gray-50 dark:even:bg-gray-900/50">
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 text-gray-500">{{ $err['hoja'] }}</td>
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 font-mono text-gray-700 dark:text-gray-300">{{ $err['fila'] }}</td>
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 font-mono text-gray-700 dark:text-gray-300">{{ $err['campo'] }}</td>
                                            <td class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-800 text-red-600 dark:text-red-400">{{ $err['error'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Sin errores en ninguna fila.
                    </p>
                @endif
            @endif

            <div class="mt-6">
                <x-filament::button wire:click="reiniciar" color="gray" icon="heroicon-o-arrow-path">
                    Nueva importación
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

</x-filament-panels::page>
