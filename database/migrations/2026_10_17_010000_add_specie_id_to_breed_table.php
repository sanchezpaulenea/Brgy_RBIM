<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Breeds belong to one species so the breed list can follow the chosen
 * species. A breed with no species (such as "Not Applicable") is shared.
 */
return new class extends Migration
{
    private const KNOWN_BREEDS = [
        'Dog' => ['Chihuahua', 'Shih Tzu', 'Golden Retriever'],
        'Cat' => ['Ragdoll'],
    ];

    public function up(): void
    {
        Schema::table('breed', function (Blueprint $table) {
            $table->integer('specie_id')->nullable()->after('breed');

            if (Schema::hasTable('specie')) {
                $table->foreign('specie_id', 'breed_specie')->references('specie_id')->on('specie');
            }
        });

        if (! Schema::hasTable('specie')) {
            return;
        }

        foreach (self::KNOWN_BREEDS as $specie => $breeds) {
            $specieId = DB::table('specie')->where('specie', $specie)->value('specie_id');

            if ($specieId === null) {
                continue;
            }

            DB::table('breed')->whereIn('breed', $breeds)->update(['specie_id' => $specieId]);
        }

        DB::table('breed')
            ->whereNull('specie_id')
            ->whereIn('breed_id', DB::table('pet_census')->select('breed_id'))
            ->orderBy('breed_id')
            ->get(['breed_id'])
            ->each(function (object $breed): void {
                $species = DB::table('pet_census')->where('breed_id', $breed->breed_id)->distinct()->pluck('specie_id');

                if ($species->count() === 1) {
                    DB::table('breed')->where('breed_id', $breed->breed_id)->update(['specie_id' => $species->first()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('breed', function (Blueprint $table) {
            if (Schema::hasTable('specie')) {
                $table->dropForeign('breed_specie');
            }

            $table->dropColumn('specie_id');
        });
    }
};
