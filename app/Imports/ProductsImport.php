<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    public function collection(Collection $rows): void
    {
        $rows = $rows
            ->map(fn ($row) => $this->normalizeRow($row->toArray()))
            ->filter(fn (array $row) => ! empty($row['kode']))
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($rows) {
            $categoryIdsByName = $this->resolveCategoryIds($rows);
            $supplierIdsByName = $this->resolveSupplierIds($rows);
            $codes = $rows->pluck('kode')->unique()->values();
            $duplicateCodes = Product::withTrashed()
                ->select('code')
                ->whereIn('code', $codes)
                ->groupBy('code')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('code');

            if ($duplicateCodes->isNotEmpty()) {
                throw new \RuntimeException(
                    'Duplicate product codes already exist in database: ' . $duplicateCodes->implode(', ')
                );
            }

            $existingProducts = Product::withTrashed()
                ->whereIn('code', $codes)
                ->get(['id', 'code', 'deleted_at'])
                ->keyBy('code');

            $trashedProductIds = $existingProducts
                ->filter(fn (Product $product) => $product->deleted_at !== null)
                ->pluck('id');

            if ($trashedProductIds->isNotEmpty()) {
                Product::withTrashed()
                    ->whereIn('id', $trashedProductIds)
                    ->restore();
            }

            $now = now();

            $insertPayload = [];
            $updatePayload = [];
            $supplierMap = [];

            foreach ($rows as $row) {
                $productData = [
                    'code'        => $row['kode'],
                    'name'        => $row['nama'],
                    'category_id' => $row['kategori'] ? ($categoryIdsByName[$row['kategori']] ?? null) : null,
                    'brand'       => $row['brand'],
                    'model'       => $row['model'],
                    'warna'       => $row['warna'],
                    'ukuran'      => $row['ukuran'],
                    'satuan'      => $row['satuan'],
                    'min_stock'   => $this->normalizeInteger($row['min_stock'], 0),
                    'lokasi'      => $row['lokasi'],
                    'harga_beli'  => $this->normalizeInteger($row['harga_beli'], 0),
                    'desc'        => $row['deskripsi'],
                    'updated_at'  => $now,
                    'deleted_at'  => null,
                ];

                $supplierIds = $this->extractSupplierIds($row['supplier'], $supplierIdsByName);

                if ($existingProducts->has($row['kode'])) {
                    $productData['id'] = $existingProducts[$row['kode']]->id;
                    $updatePayload[$row['kode']] = $productData;
                } else {
                    $productData['created_at'] = $now;
                    $insertPayload[$row['kode']] = $productData;
                }

                if ($supplierIds !== null) {
                    $supplierMap[$row['kode']] = $supplierIds;
                }
            }

            if (! empty($insertPayload)) {
                Product::insert(array_values($insertPayload));
            }

            if (! empty($updatePayload)) {
                Product::upsert(
                    array_values($updatePayload),
                    ['id'],
                    [
                        'name',
                        'category_id',
                        'brand',
                        'model',
                        'warna',
                        'ukuran',
                        'satuan',
                        'min_stock',
                        'lokasi',
                        'harga_beli',
                        'desc',
                        'updated_at',
                        'deleted_at',
                    ]
                );
            }

            $this->syncSuppliers($supplierMap);
        });
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function resolveCategoryIds(Collection $rows): array
    {
        $categoryNames = $rows->pluck('kategori')
            ->filter()
            ->unique()
            ->values();

        if ($categoryNames->isEmpty()) {
            return [];
        }

        $this->ensureUniqueNames(Category::class, $categoryNames, 'category');

        Category::withTrashed()
            ->whereIn('name', $categoryNames)
            ->whereNotNull('deleted_at')
            ->restore();

        $existingNames = Category::whereIn('name', $categoryNames)
            ->pluck('name')
            ->all();

        $missingNames = $categoryNames
            ->diff($existingNames)
            ->values();

        if ($missingNames->isNotEmpty()) {
            $timestamp = now();
            Category::insert(
                $missingNames
                    ->map(fn (string $name) => [
                        'name'       => $name,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])
                    ->all()
            );
        }

        return Category::whereIn('name', $categoryNames)
            ->pluck('id', 'name')
            ->all();
    }

    private function resolveSupplierIds(Collection $rows): array
    {
        $supplierNames = $rows
            ->pluck('supplier')
            ->filter()
            ->flatMap(fn (string $supplierList) => collect(explode(',', $supplierList)))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->values();

        if ($supplierNames->isEmpty()) {
            return [];
        }

        $this->ensureUniqueNames(Supplier::class, $supplierNames, 'supplier');

        Supplier::withTrashed()
            ->whereIn('name', $supplierNames)
            ->whereNotNull('deleted_at')
            ->restore();

        $existingNames = Supplier::whereIn('name', $supplierNames)
            ->pluck('name')
            ->all();

        $missingNames = $supplierNames
            ->diff($existingNames)
            ->values();

        if ($missingNames->isNotEmpty()) {
            $timestamp = now();
            $supplierCodes = $this->generateSupplierCodes($missingNames->count());

            Supplier::insert(
                $missingNames
                    ->values()
                    ->map(fn (string $name, int $index) => [
                        'kode_supplier' => $supplierCodes[$index],
                        'name'          => $name,
                        'alamat'        => '-',
                        'no_telp'       => '-',
                        'created_at'    => $timestamp,
                        'updated_at'    => $timestamp,
                    ])
                    ->all()
            );
        }

        return Supplier::whereIn('name', $supplierNames)
            ->pluck('id', 'name')
            ->all();
    }

    private function ensureUniqueNames(string $modelClass, Collection $names, string $entity): void
    {
        $duplicateNames = $modelClass::withTrashed()
            ->select('name')
            ->whereIn('name', $names)
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        if ($duplicateNames->isNotEmpty()) {
            throw new \RuntimeException(
                'Duplicate ' . $entity . ' names already exist in database: ' . $duplicateNames->implode(', ')
            );
        }
    }

    private function generateSupplierCodes(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $lastCode = Supplier::withTrashed()
            ->where('kode_supplier', 'like', 'S%')
            ->orderByRaw('CAST(SUBSTRING(kode_supplier, 2) AS UNSIGNED) DESC')
            ->value('kode_supplier');

        $nextNumber = $lastCode ? ((int) substr($lastCode, 1)) + 1 : 1;
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = 'S' . str_pad((string) ($nextNumber + $i), 5, '0', STR_PAD_LEFT);
        }

        return $codes;
    }

    private function syncSuppliers(array $supplierMap): void
    {
        if ($supplierMap === []) {
            return;
        }

        $productsByCode = Product::whereIn('code', array_keys($supplierMap))
            ->get(['id', 'code'])
            ->keyBy('code');

        $productIdsToReplace = [];
        $pivotRows = [];

        foreach ($supplierMap as $code => $supplierIds) {
            $product = $productsByCode->get($code);

            if (! $product) {
                continue;
            }

            $productIdsToReplace[] = $product->id;

            foreach (array_unique($supplierIds) as $supplierId) {
                $pivotRows[] = [
                    'product_id'  => $product->id,
                    'supplier_id' => $supplierId,
                ];
            }
        }

        if ($productIdsToReplace === []) {
            return;
        }

        DB::table('product_supplier')
            ->whereIn('product_id', $productIdsToReplace)
            ->delete();

        if ($pivotRows !== []) {
            foreach (array_chunk($pivotRows, 1000) as $chunk) {
                DB::table('product_supplier')->insert($chunk);
            }
        }
    }

    private function extractSupplierIds(?string $supplierList, array $supplierIdsByName): ?array
    {
        if ($supplierList === null) {
            return null;
        }

        $supplierIds = collect(explode(',', $supplierList))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->map(fn (string $name) => $supplierIdsByName[$name] ?? null)
            ->filter()
            ->values()
            ->all();

        return $supplierIds;
    }

    private function normalizeRow(array $row): array
    {
        return [
            'kode'       => $this->normalizeString($row['kode'] ?? null),
            'nama'       => $this->normalizeString($row['nama'] ?? null),
            'kategori'   => $this->normalizeString($row['kategori'] ?? null),
            'brand'      => $this->normalizeString($row['brand'] ?? null),
            'model'      => $this->normalizeString($row['model'] ?? null),
            'warna'      => $this->normalizeString($row['warna'] ?? null),
            'ukuran'     => $this->normalizeString($row['ukuran'] ?? null),
            'satuan'     => $this->normalizeString($row['satuan'] ?? null),
            'min_stock'  => $row['min_stock'] ?? null,
            'lokasi'     => $this->normalizeString($row['lokasi'] ?? null),
            'harga_beli' => $row['harga_beli'] ?? null,
            'deskripsi'  => $this->normalizeString($row['deskripsi'] ?? null),
            'supplier'   => $this->normalizeString($row['supplier'] ?? null),
        ];
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeInteger(mixed $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $normalized = preg_replace('/[^\d-]/', '', (string) $value);

        if ($normalized === null || $normalized === '' || $normalized === '-') {
            return $default;
        }

        return (int) $normalized;
    }
}
