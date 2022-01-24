<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = [
            [
                'menuname' => 'Home',
                'menuseq' => '10',
                'menuparent' => '0',
                'menuicon' => 'fas fa-home',
                'aco_id' => '0',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => 'dashboard',
                'menukode' => '0',
            ],
            [
                'menuname' => 'Logout',
                'menuseq' => '90',
                'menuparent' => '0',
                'menuicon' => 'icon-out',
                'aco_id' => '5',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => 'logout',
                'menukode' => '9',
            ],
            [
                'menuname' => 'Master',
                'menuseq' => '20',
                'menuparent' => '0',
                'menuicon' => 'fas fa-list',
                'aco_id' => '0',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '1',
            ],
            [
                'menuname' => 'User',
                'menuseq' => '20',
                'menuparent' => '5',
                'menuicon' => 'fas fa-user',
                'aco_id' => '1',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '161',
            ],
            [
                'menuname' => 'System',
                'menuseq' => '100',
                'menuparent' => '3',
                'menuicon' => 'fab fa-ubuntu',
                'aco_id' => '0',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '16',
            ],
            [
                'menuname' => 'Role',
                'menuseq' => '30',
                'menuparent' => '5',
                'menuicon' => 'fas fa-user-tag',
                'aco_id' => '6',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '162',
            ],
            [
                'menuname' => 'Menu',
                'menuseq' => '31',
                'menuparent' => '5',
                'menuicon' => 'fas fa-bars',
                'aco_id' => '10',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '163',
            ],
            [
                'menuname' => 'Parameter',
                'menuseq' => '32',
                'menuparent' => '5',
                'menuicon' => 'fas fa-exclamation',
                'aco_id' => '14',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '164',
            ],
            [
                'menuname' => 'User Role',
                'menuseq' => '33',
                'menuparent' => '5',
                'menuicon' => 'fas fa-user-tag',
                'aco_id' => '18',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '165',
            ],
            [
                'menuname' => 'User Menu',
                'menuseq' => '34',
                'menuparent' => '5',
                'menuicon' => 'fas fa-user-minus',
                'aco_id' => '22',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '166',
            ],
            [
                'menuname' => 'Error',
                'menuseq' => '35',
                'menuparent' => '5',
                'menuicon' => 'fas fa-bug',
                'aco_id' => '26',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '167',
            ],
            [
                'menuname' => 'Hak Role',
                'menuseq' => '36',
                'menuparent' => '5',
                'menuicon' => 'fas fa-user-tag',
                'aco_id' => '30',
                'modifiedby' => 'admin',
                'link' => '',
                'menuexe' => '',
                'menukode' => '168',
            ],
        ];
        
        foreach ($menus as $menu ) {
            Menu::create($menu);
        }
    }
}
