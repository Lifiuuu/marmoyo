<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuTopping;
use App\Models\MenuVariant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with authentic Kedai Mie Marmoyo data.
     */
    public function run(): void
    {
        // 1. Users (Admin & Kasir)
        $owner = User::firstOrCreate([
            'email' => 'owner@marmoyo.com',
        ], [
            'name' => 'Wasis (Owner)',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $cashier = User::firstOrCreate([
            'email' => 'kasir@marmoyo.com',
        ], [
            'name' => 'Najwa (Kasir)',
            'role' => 'kasir',
            'password' => Hash::make('password123'),
        ]);

        // 2. Restaurant Tables (Meja 01 s/d Meja 12)
        for ($i = 1; $i <= 12; $i++) {
            $tableNum = sprintf('%02d', $i);
            RestaurantTable::firstOrCreate([
                'table_number' => $tableNum,
            ], [
                'qr_token' => RestaurantTable::generateTokenForTable($tableNum),
                'status' => 'available',
            ]);
        }

        // 3. Toppings
        $toppings = [
            ['name' => 'Ekstra Pangsit Goreng Krispi (3pcs)', 'price' => 3000],
            ['name' => 'Ekstra Telur Mata Sapi 1/2 Matang', 'price' => 4000],
            ['name' => 'Ekstra Daging Ayam Charsiu', 'price' => 5000],
        ];
        foreach ($toppings as $topping) {
            MenuTopping::firstOrCreate(['name' => $topping['name']], $topping);
        }

        // 4. Categories
        $catMie = Category::firstOrCreate(['slug' => 'aneka-mie-marmoyo'], [
            'name' => 'Aneka Mie Marmoyo',
            'icon' => '🍜',
            'sort_order' => 1,
        ]);

        $catDimsum = Category::firstOrCreate(['slug' => 'sidesh-dimsum-gurih'], [
            'name' => 'Sidesh & Dimsum Gurih',
            'icon' => '🥟',
            'sort_order' => 2,
        ]);

        $catMinuman = Category::firstOrCreate(['slug' => 'minuman-segar-pereda-pedas'], [
            'name' => 'Minuman Segar Pereda Pedas',
            'icon' => '🥤',
            'sort_order' => 3,
        ]);

        // 5. Menus - Aneka Mie
        $mieKuahMala = Menu::firstOrCreate(['slug' => 'mie-kuah-mala-pedas-nampol'], [
            'category_id' => $catMie->id,
            'name' => 'Mie Kuah Mala Pedas Nampol',
            'description' => 'Sensasi kuah rempah Mala khas Marmoyo dengan perpaduan minyak cabai Sichuan otentik dan kaldu ayam kental gurih.',
            'base_price' => 15000,
            'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => true,
            'default_spiciness_level' => 3,
            'is_available' => true,
            'is_bestseller' => true,
        ]);

        $mieCharsiu = Menu::firstOrCreate(['slug' => 'mie-charsiu'], [
            'category_id' => $catMie->id,
            'name' => 'Mie Charsiu',
            'description' => 'Mie kenyal gurih disajikan dengan topping ayam charsiu manis gurih berbumbu oriental.',
            'base_price' => 15000,
            'image_url' => 'https://images.unsplash.com/photo-1552611052-33e04de081de?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        $mieTomyam = Menu::firstOrCreate(['slug' => 'mie-tomyam'], [
            'category_id' => $catMie->id,
            'name' => 'Mie Tomyam',
            'description' => 'Kuah tomyam asam pedas segar dengan aroma serai dan jeruk purut yang menggugah selera.',
            'base_price' => 15000,
            'image_url' => 'https://images.unsplash.com/photo-1548946526-f69e2424cf45?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => true,
            'default_spiciness_level' => 2,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        $mieGurih13 = Menu::firstOrCreate(['slug' => 'mie-gurih-level-1-3'], [
            'category_id' => $catMie->id,
            'name' => 'Mie Gurih Level 1-3',
            'description' => 'Mie kering bumbu bawang gurih dengan tingkat kepedasan pas favorit para pelanggan setia.',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => true,
            'default_spiciness_level' => 2,
            'is_available' => true,
            'is_bestseller' => true,
        ]);

        $mieGurih45 = Menu::firstOrCreate(['slug' => 'mie-gurih-level-4-5'], [
            'category_id' => $catMie->id,
            'name' => 'Mie Gurih Level 4-5',
            'description' => 'Tantangan pedas super rawit setan yang membakar lidah, khusus pecinta sensasi pedas ekstrim.',
            'base_price' => 11000,
            'image_url' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => true,
            'default_spiciness_level' => 5,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        // Attach Variants to Mie Menus
        $noodleMenus = [$mieKuahMala, $mieCharsiu, $mieTomyam, $mieGurih13, $mieGurih45];
        foreach ($noodleMenus as $menu) {
            // Spiciness variants
            $spicinessLevels = [
                ['name' => 'Level 1 (Santai / Pedas Ringan)', 'price' => 0],
                ['name' => 'Level 2 (Sedang / Favorit Pelanggan)', 'price' => 0],
                ['name' => 'Level 3 (Pedas Nampol Kebon)', 'price' => 0],
                ['name' => 'Level 4-5 (Super Pedas Rawit Setan)', 'price' => 1000],
            ];
            foreach ($spicinessLevels as $sp) {
                MenuVariant::firstOrCreate([
                    'menu_id' => $menu->id,
                    'group_name' => 'Tingkat Kepedasan',
                    'name' => $sp['name'],
                ], [
                    'additional_price' => $sp['price'],
                ]);
            }

            // Noodle type variants
            $noodleTypes = [
                ['name' => 'Mie Keriting Kenyal', 'price' => 0],
                ['name' => 'Mie Lebar Lembut', 'price' => 0],
            ];
            foreach ($noodleTypes as $nt) {
                MenuVariant::firstOrCreate([
                    'menu_id' => $menu->id,
                    'group_name' => 'Jenis Mie',
                    'name' => $nt['name'],
                ], [
                    'additional_price' => $nt['price'],
                ]);
            }
        }

        // 6. Menus - Sidesh & Dimsum Gurih
        Menu::firstOrCreate(['slug' => 'udang-keju'], [
            'category_id' => $catDimsum->id,
            'name' => 'Udang Keju',
            'description' => 'Olahan daging udang lembut dibalut tepung renyah dengan isian lelehan keju mozarella gurih di dalamnya (3 pcs).',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => true,
        ]);

        Menu::firstOrCreate(['slug' => 'udang-rambutan'], [
            'category_id' => $catDimsum->id,
            'name' => 'Udang Rambutan',
            'description' => 'Bola olahan udang dan daging ayam dengan balutan irisan kulit pangsit krispi menyerupai rambut (3 pcs).',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        Menu::firstOrCreate(['slug' => 'dimsum-ayam-pangsit'], [
            'category_id' => $catDimsum->id,
            'name' => 'Dimsum Ayam Pangsit',
            'description' => 'Dimsum pangsit goreng krispi dengan isian daging ayam cincang padat dan saus cocolan gurih khas.',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => true,
        ]);

        Menu::firstOrCreate(['slug' => 'ceker-pedas'], [
            'category_id' => $catDimsum->id,
            'name' => 'Ceker Pedas',
            'description' => 'Ceker ayam presto empuk tanpa perlawanan dengan siraman kuah cabai pedas merona.',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => true,
            'default_spiciness_level' => 4,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        // 7. Menus - Minuman Segar Pereda Pedas
        Menu::firstOrCreate(['slug' => 'es-teh-manis-jumbo'], [
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh Manis Jumbo',
            'description' => 'Teh melati wangi diseduh segar dingin berukuran jumbo, sangat ampuh menetralisir rasa pedas.',
            'base_price' => 3000,
            'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => true,
        ]);

        Menu::firstOrCreate(['slug' => 'es-jeruk-peras'], [
            'category_id' => $catMinuman->id,
            'name' => 'Es Jeruk Peras',
            'description' => 'Air perasan jeruk segar murni berpadu dengan es batu dingin dan gula tebu asli.',
            'base_price' => 4000,
            'image_url' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => false,
        ]);

        Menu::firstOrCreate(['slug' => 'cokelat-kuantel'], [
            'category_id' => $catMinuman->id,
            'name' => 'Cokelat Kuantel',
            'description' => 'Minuman olahan cokelat hitam manis legit dengan kekentalan pekat dingin yang memanjakan lidah.',
            'base_price' => 10000,
            'image_url' => 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?w=600&auto=format&fit=crop&q=80',
            'is_spicy' => false,
            'default_spiciness_level' => 0,
            'is_available' => true,
            'is_bestseller' => false,
        ]);
    }
}
