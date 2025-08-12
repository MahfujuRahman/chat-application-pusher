<?php

namespace App\Modules\Management\Dashboard\Controller;

use App\Modules\Management\Dashboard\Actions\GetAllDashboardData;
use App\Modules\Management\Dashboard\Actions\GetEmployeeDashboardData;

use App\Http\Controllers\Controller as ControllersController;


class Controller extends ControllersController
{

    public function GetAllDashboardData( ){

        $data = GetAllDashboardData::execute();
        return $data;
    }
    public function GetEmployeeDashboardData( ){

        $data = GetEmployeeDashboardData::execute();
        return $data;
    }

   

}