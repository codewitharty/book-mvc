<?php
namespace App\Http\Controllers;

use Exception;

class ShowHomePageController
{
    /**
     * @throws Exception
     */
    public function handle(): string
    {
        return view('home', ['number' => 42]);
    }
}