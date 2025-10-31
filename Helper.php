<?php

namespace App\Core;

class Helper
{

    public function dd($something)
    {
        echo '<pre>';
        print_r($something);
        echo '</pre>';
        exit;
    }

}