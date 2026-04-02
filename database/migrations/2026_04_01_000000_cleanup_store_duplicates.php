<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('stores')
            ->select('ruc', DB::raw('COUNT(*) as count'))
            ->whereNotNull('ruc')
            ->groupBy('ruc')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $stores = DB::table('stores')
                ->where('ruc', $dup->ruc)
                ->orderBy('created_at')
                ->get();

            $keep = $stores->first();
            $toDelete = $stores->skip(1);

            foreach ($toDelete as $store) {
                DB::table('stores')
                    ->where('id', $store->id)
                    ->update([
                        'ruc' => $store->ruc.'_dup_'.$store->id,
                    ]);
            }
        }

        DB::table('stores')
            ->where(function ($query) {
                $query->whereNull('trade_name')
                    ->whereNotNull('nombre_comercial');
            })
            ->update(['trade_name' => DB::raw('nombre_comercial')]);

        DB::table('stores')
            ->where(function ($query) {
                $query->whereNull('nombre_comercial')
                    ->whereNotNull('trade_name');
            })
            ->update(['nombre_comercial' => DB::raw('trade_name')]);

        DB::table('stores')
            ->where(function ($query) {
                $query->whereNull('address')
                    ->whereNotNull('direccion_fiscal');
            })
            ->update(['address' => DB::raw('direccion_fiscal')]);

        DB::table('stores')
            ->where(function ($query) {
                $query->whereNull('direccion_fiscal')
                    ->whereNotNull('address');
            })
            ->update(['direccion_fiscal' => DB::raw('address')]);

        DB::statement('UPDATE stores SET store_name = trade_name WHERE store_name IS NULL OR store_name = ""');
    }

    public function down(): void {}
};
