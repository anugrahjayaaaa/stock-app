<?php

namespace Database\Seeders;

use App\Models\Broker;
use Illuminate\Database\Seeder;

class BrokerSeeder extends Seeder
{
    // Broker master from public IDX data (refs: idx.co.id ringkasan-broker, cermati daftar kode broker).
    // category: asing = foreign-owned, bumn = state-owned, swasta = local private.
    // ponytail: covers brokers used across widgets + common top brokers; extend lazily as new codes appear.
    private const BROKERS = [
        // BUMN
        ['CC', 'Mandiri Sekuritas', 'bumn'],
        ['NI', 'BNI Sekuritas', 'bumn'],
        ['DH', 'Danareksa Sekuritas', 'bumn'],
        ['DX', 'Bahana Sekuritas', 'bumn'],
        ['BH', 'Bumi Putera Sekuritas', 'bumn'],
        // Asing
        ['AK', 'UBS Sekuritas Indonesia', 'asing'],
        ['BK', 'JP Morgan Sekuritas Indonesia', 'asing'],
        ['KZ', 'CLSA Sekuritas Indonesia', 'asing'],
        ['RX', 'Macquarie Sekuritas Indonesia', 'asing'],
        ['CS', 'Credit Suisse Sekuritas Indonesia', 'asing'],
        ['ZP', 'Maybank Sekuritas Indonesia', 'asing'],
        ['YP', 'Mirae Asset Sekuritas Indonesia', 'asing'],
        ['YU', 'CGS-CIMB Sekuritas Indonesia', 'asing'],
        ['BQ', 'Korea Investment & Securities Indonesia', 'asing'],
        ['TP', 'OCBC Sekuritas Indonesia', 'asing'],
        ['AH', 'Shinhan Sekuritas Indonesia', 'asing'],
        ['AG', 'Kiwoom Sekuritas Indonesia', 'asing'],
        ['HD', 'KGI Sekuritas Indonesia', 'asing'],
        ['GW', 'HSBC Sekuritas Indonesia', 'asing'],
        ['BC', 'BNP Paribas Sekuritas Indonesia', 'asing'],
        ['DR', 'RHB Sekuritas Indonesia', 'asing'],
        ['DU', 'KAF Sekuritas Indonesia', 'asing'],
        // Swasta Lokal
        ['PD', 'Indo Premier Sekuritas', 'swasta'],
        ['AZ', 'Sucor Sekuritas', 'swasta'],
        ['XL', 'Mahakarya Artha Sekuritas', 'swasta'],
        ['XC', 'Ajaib Sekuritas Asia', 'swasta'],
        ['NH', 'NH Korindo Sekuritas Indonesia', 'swasta'],
        ['SQ', 'BCA Sekuritas', 'swasta'],
        ['AR', 'Binaartha Sekuritas', 'swasta'],
        ['AT', 'Phintraco Sekuritas', 'swasta'],
        ['BS', 'Equity Sekuritas Indonesia', 'swasta'],
        ['EP', 'MNC Sekuritas', 'swasta'],
        ['BR', 'Trust Sekuritas', 'swasta'],
        ['SA', 'Elit Sukses Sekuritas', 'swasta'],
        ['SS', 'Supra Sekuritas Indonesia', 'swasta'],
        ['SF', 'Surya Fajar Sekuritas', 'swasta'],
        ['TF', 'Universal Broker Indonesia', 'swasta'],
        ['PP', 'Aldiracita Sekuritas Indonesia', 'swasta'],
        ['CP', 'KB Valbury Sekuritas', 'swasta'],
        ['KK', 'Phillip Sekuritas Indonesia', 'swasta'],
    ];

    public function run(): void
    {
        foreach (self::BROKERS as [$code, $name, $category]) {
            Broker::updateOrCreate(['code' => $code], ['name' => $name, 'category' => $category]);
        }
    }
}
