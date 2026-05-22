<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    /**
     * 2 colunas — widgets de stats ficam full-width, charts side-by-side.
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm'      => 1,
            'md'      => 2,
            'xl'      => 2,
        ];
    }
}
