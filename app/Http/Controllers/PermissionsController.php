<?php

namespace App\Http\Controllers;

use App\models\Permission;
use App\models\Factel;
use Illuminate\Support\Facades\Auth;

class PermissionsController extends Controller {

    public static function hasAnyRole($roles) {

        if ($roles == 'factel') {
            $emisor = Factel::all();
            $empresa = $emisor->first();

            if (!empty($empresa)) {
                return $empresa->status;
            }
        }
        $user_id = Auth::user()->id;
        $info = Permission::where($roles, 1)->where('user_id', $user_id)->count();

        if ($info > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function authorizeRoles($roles) {
        if (PermissionsController::hasAnyRole($roles)) {
            return true;
        }
        return ['status' => 401, 'message' => 'Esta acción no está autorizada.'];
    }

}
