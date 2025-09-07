<?php

namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class Helper
{
	// public static function profileData()
	// {
	// 	$id = session('id');
	// 	$data = DB::table('users')->select('users.*', 'role.name as role')
	// 	->leftjoin('role_user', 'role_user.id_user', '=', 'users.id')
	// 	->leftjoin('role', 'role.id', '=', 'role_user.id_role')
	// 	->where('users.id', $id)->get();
	// 	$result['image'] = $data[0]->image;
	// 	$result['role'] = $data[0]->role;
	// 	return $result;
	// }


	public static function adminBalance()
	{
		$id = session('id');
		$data = DB::table('customers')->select('balance')->where('id', $id)->first();

		if ($data) {
			return ['balance' => $data->balance];
		} else {
			return ['balance' => 0]; // or handle it differently
		}
	}

	public static function permissions()
	{
		$rtData=array();
		$id = session('id');
		$designation=0;
		$user = DB::table('users')->select('designation')->where('id', $id)->first();
		if($user)
		{
			$designation=$user->designation;
		}
		
		$data = DB::table('role_permission')->select('menu')->where('permission', 1)->where('role_id', $designation)->get();
		foreach ($data as $key => $value) {
			$rtData[]=$value->menu;
		}
		
		$tdata["type"]="login permission ".$id;
		$tdata["testdata"]=json_encode($rtData);
		DB::table('test2')->insert($tdata);
		
		return $rtData;
	}

	//     public static function getUserMenus()
    // {
    //     $role_id = DB::table('role_user')->where('id_user', session('id'))->pluck('id_role');

    //     // Join menus with role_permission and sort alphabetically
    //     $menus = DB::table('menu')
    //         ->leftJoin('role_permission as rp', function($join) use ($role_id) {
    //             $join->on('menu.menu', '=', 'rp.menu')
    //                 ->where('rp.role_id', $role_id);
    //         })
    //         ->select('menu.id', 'menu.menu', 'menu.icon_name', 'rp.permission')
    //         ->orderBy('menu.menu', 'asc')
    //         ->get();

    //     // Join submenus with role_permission and sort alphabetically
    //     $submenus = DB::table('sub_menu')
    //         ->leftJoin('role_permission as rp', function($join) use ($role_id) {
    //             $join->on('sub_menu.menu', '=', 'rp.menu')
    //                 ->where('rp.role_id', $role_id);
    //         })
    //         ->select('sub_menu.id', 'sub_menu.menu', 'sub_menu.menu_id', 'sub_menu.url', 'rp.permission')
    //         ->orderBy('sub_menu.menu', 'asc')
    //         ->get();

    //     // Group submenus under their parent menu
    //     $menuArray = [];
    //     foreach ($menus as $menu) {
    //         if ($menu->permission == 1) {
    //             $menu->submenu = $submenus->filter(function($sub) use ($menu) {
    //                 return $sub->menu_id == $menu->id && $sub->permission == 1;
    //             })->sortBy('menu')->values(); // Sort submenus alphabetically

    //             $menuArray[] = $menu;
    //         }
    //     }

    //     return $menuArray;
    // }

public static function getUserMenus()
{
    // Get the user's role ID
    $role_id = DB::table('role_user')->where('id_user', session('id'))->value('id_role');

    // Get role permissions as a key-value array: menu => permission
    $permissions = DB::table('role_permission')
        ->where('role_id', $role_id)
        ->pluck('permission', 'menu'); // ['Menu Name' => 1, ...]

    // Get all menus
    $menus = DB::table('menu')
        ->select('id', 'menu', 'icon_name')
        ->get();

    // Get all submenus
    $submenus = DB::table('sub_menu')
        ->select('id', 'menu', 'menu_id', 'url')
        ->get();

    $menuArray = [];

    foreach ($menus as $menu) {
        // Assign permission for this menu
        $menu->permission = $permissions[$menu->menu] ?? 0;

        // Only include menus that the role has permission for
        if ($menu->permission == 1) {

            // Attach only submenus that the role has permission for
            $menu->submenu = $submenus->filter(function($sub) use ($menu, $permissions) {
                return $sub->menu_id == $menu->id && ($permissions[$sub->menu] ?? 0) == 1;
            })->values();

            $menuArray[] = $menu;
        }
    }

    return $menuArray;
}
}