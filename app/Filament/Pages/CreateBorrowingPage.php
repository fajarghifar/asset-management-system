<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CreateBorrowingPage extends Page
{
    protected string $view = 'filament.pages.create-borrowing-form';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return '';
    }
}
