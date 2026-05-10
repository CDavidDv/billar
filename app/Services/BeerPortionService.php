<?php

namespace App\Services;

use App\Models\AddIn;
use App\Models\BeerPour;
use App\Models\Caguama;
use App\Models\MicheladaRecipe;
use Illuminate\Support\Collection;

class BeerPortionService
{
    const CAGUAMA_VOLUME_ML = 1200;

    public function getActiveCaguamas(): Collection
    {
        return Caguama::where('status', 'active')
            ->where('branch_id', session('branch_id'))
            ->with(['openedBy', 'pours'])
            ->orderBy('opened_at')
            ->get();
    }

    public function openNewCaguama(int $userId, ?string $notes = null): Caguama
    {
        return Caguama::create([
            'total_volume_ml' => self::CAGUAMA_VOLUME_ML,
            'remaining_volume_ml' => self::CAGUAMA_VOLUME_ML,
            'opened_at' => now(),
            'opened_by' => $userId,
            'status' => 'active',
            'notes' => $notes,
            'branch_id' => session('branch_id'),
        ]);
    }

    public function canPour(Caguama $caguama, int $volumeNeeded): bool
    {
        return $caguama->status === 'active'
            && $caguama->remaining_volume_ml >= $volumeNeeded;
    }

    public function calcBeerVolume(MicheladaRecipe $recipe, array $addInIds): int
    {
        $displaced = AddIn::findMany($addInIds)->sum('volume_ml');

        return max(0, $recipe->container_volume_ml - $displaced);
    }

    public function pour(
        Caguama $caguama,
        MicheladaRecipe $recipe,
        int $userId,
        array $addInIds = [],
        ?int $orderItemId = null
    ): BeerPour {
        $beerVolume = $this->calcBeerVolume($recipe, $addInIds);

        if (! $this->canPour($caguama, $beerVolume)) {
            throw new \RuntimeException('Remanente insuficiente en caguama #'.$caguama->id.'.');
        }

        $pour = BeerPour::create([
            'caguama_id' => $caguama->id,
            'michelada_recipe_id' => $recipe->id,
            'volume_ml' => $beerVolume,
            'poured_by' => $userId,
            'order_item_id' => $orderItemId,
        ]);

        if ($addInIds) {
            $addIns = AddIn::findMany($addInIds);
            foreach ($addIns as $addIn) {
                $pour->addIns()->attach($addIn->id, ['volume_ml' => $addIn->volume_ml]);
            }
        }

        $caguama->remaining_volume_ml = max(0, $caguama->remaining_volume_ml - $beerVolume);

        if ($caguama->remaining_volume_ml === 0) {
            $caguama->status = 'empty';
        }

        $caguama->save();

        return $pour;
    }

    public function closeCaguama(Caguama $caguama): void
    {
        $caguama->update(['status' => 'empty']);
    }

    public function tarrosPosibles(Caguama $caguama, MicheladaRecipe $recipe): int
    {
        $vol = $recipe->container_volume_ml ?: $recipe->beer_volume_ml;
        if ($vol <= 0) {
            return 0;
        }

        return (int) floor($caguama->remaining_volume_ml / $vol);
    }

    public function getStats(): array
    {
        $total = Caguama::count();
        $active = Caguama::where('status', 'active')->count();
        $totalPours = BeerPour::count();
        $avgPoursPerCaguama = $total > 0 ? round($totalPours / $total, 1) : 0;

        return [
            'total_caguamas' => $total,
            'active_caguamas' => $active,
            'total_pours' => $totalPours,
            'avg_pours_per_caguama' => $avgPoursPerCaguama,
        ];
    }
}
