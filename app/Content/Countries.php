<?php

namespace App\Content;

/**
 * Countries for Flag Challenge and Capital Challenge.
 * code (ISO 3166-1 alpha-2, also the flag file name) => [name, capital, continent, easy].
 * capital = null where the answer is not one clear city (several official capitals or a
 * capital move in progress); those countries appear in Flag Challenge only.
 * Flags: public/images/flags/{code}.svg from lipis/flag-icons (MIT).
 */
class Countries
{
    public const CONTINENTS = ['AS' => 'Asia', 'EU' => 'Europe', 'AF' => 'Africa', 'NA' => 'North America', 'SA' => 'South America', 'OC' => 'Oceania'];

    public const ALL = [
        // Asia
        'pk' => ['Pakistan', 'Islamabad', 'AS', true], 'in' => ['India', 'New Delhi', 'AS', true], 'bd' => ['Bangladesh', 'Dhaka', 'AS', true],
        'cn' => ['China', 'Beijing', 'AS', true], 'jp' => ['Japan', 'Tokyo', 'AS', true], 'kr' => ['South Korea', 'Seoul', 'AS', true],
        'kp' => ['North Korea', 'Pyongyang', 'AS', false], 'af' => ['Afghanistan', 'Kabul', 'AS', true], 'ae' => ['United Arab Emirates', 'Abu Dhabi', 'AS', true],
        'sa' => ['Saudi Arabia', 'Riyadh', 'AS', true], 'ir' => ['Iran', 'Tehran', 'AS', true], 'iq' => ['Iraq', 'Baghdad', 'AS', false],
        'tr' => ['Turkey', 'Ankara', 'AS', true], 'qa' => ['Qatar', 'Doha', 'AS', true], 'kw' => ['Kuwait', 'Kuwait City', 'AS', false],
        'om' => ['Oman', 'Muscat', 'AS', false], 'bh' => ['Bahrain', 'Manama', 'AS', false], 'jo' => ['Jordan', 'Amman', 'AS', false],
        'lb' => ['Lebanon', 'Beirut', 'AS', false], 'sy' => ['Syria', 'Damascus', 'AS', false], 'np' => ['Nepal', 'Kathmandu', 'AS', true],
        'bt' => ['Bhutan', 'Thimphu', 'AS', false], 'mv' => ['Maldives', 'Malé', 'AS', false], 'lk' => ['Sri Lanka', null, 'AS', true],
        'mm' => ['Myanmar', 'Naypyidaw', 'AS', false], 'th' => ['Thailand', 'Bangkok', 'AS', true], 'vn' => ['Vietnam', 'Hanoi', 'AS', false],
        'kh' => ['Cambodia', 'Phnom Penh', 'AS', false], 'la' => ['Laos', 'Vientiane', 'AS', false], 'my' => ['Malaysia', 'Kuala Lumpur', 'AS', true],
        'sg' => ['Singapore', 'Singapore', 'AS', true], 'ph' => ['Philippines', 'Manila', 'AS', false], 'id' => ['Indonesia', null, 'AS', true],
        'mn' => ['Mongolia', 'Ulaanbaatar', 'AS', false], 'kz' => ['Kazakhstan', 'Astana', 'AS', false], 'uz' => ['Uzbekistan', 'Tashkent', 'AS', false],
        'tm' => ['Turkmenistan', 'Ashgabat', 'AS', false], 'kg' => ['Kyrgyzstan', 'Bishkek', 'AS', false], 'tj' => ['Tajikistan', 'Dushanbe', 'AS', false],
        'az' => ['Azerbaijan', 'Baku', 'AS', false], 'ge' => ['Georgia', 'Tbilisi', 'AS', false], 'am' => ['Armenia', 'Yerevan', 'AS', false],
        // Europe
        'gb' => ['United Kingdom', 'London', 'EU', true], 'ie' => ['Ireland', 'Dublin', 'EU', false], 'fr' => ['France', 'Paris', 'EU', true],
        'de' => ['Germany', 'Berlin', 'EU', true], 'it' => ['Italy', 'Rome', 'EU', true], 'es' => ['Spain', 'Madrid', 'EU', true],
        'pt' => ['Portugal', 'Lisbon', 'EU', true], 'nl' => ['Netherlands', 'Amsterdam', 'EU', true], 'be' => ['Belgium', 'Brussels', 'EU', false],
        'ch' => ['Switzerland', 'Bern', 'EU', true], 'at' => ['Austria', 'Vienna', 'EU', false], 'se' => ['Sweden', 'Stockholm', 'EU', true],
        'no' => ['Norway', 'Oslo', 'EU', true], 'dk' => ['Denmark', 'Copenhagen', 'EU', false], 'fi' => ['Finland', 'Helsinki', 'EU', false],
        'is' => ['Iceland', 'Reykjavík', 'EU', false], 'pl' => ['Poland', 'Warsaw', 'EU', false], 'cz' => ['Czechia', 'Prague', 'EU', false],
        'sk' => ['Slovakia', 'Bratislava', 'EU', false], 'hu' => ['Hungary', 'Budapest', 'EU', false], 'ro' => ['Romania', 'Bucharest', 'EU', false],
        'bg' => ['Bulgaria', 'Sofia', 'EU', false], 'gr' => ['Greece', 'Athens', 'EU', true], 'rs' => ['Serbia', 'Belgrade', 'EU', false],
        'hr' => ['Croatia', 'Zagreb', 'EU', false], 'si' => ['Slovenia', 'Ljubljana', 'EU', false], 'ba' => ['Bosnia and Herzegovina', 'Sarajevo', 'EU', false],
        'al' => ['Albania', 'Tirana', 'EU', false], 'mk' => ['North Macedonia', 'Skopje', 'EU', false], 'me' => ['Montenegro', 'Podgorica', 'EU', false],
        'ua' => ['Ukraine', 'Kyiv', 'EU', false], 'by' => ['Belarus', 'Minsk', 'EU', false], 'ru' => ['Russia', 'Moscow', 'EU', true],
        'lt' => ['Lithuania', 'Vilnius', 'EU', false], 'lv' => ['Latvia', 'Riga', 'EU', false], 'ee' => ['Estonia', 'Tallinn', 'EU', false],
        'md' => ['Moldova', 'Chișinău', 'EU', false], 'lu' => ['Luxembourg', 'Luxembourg', 'EU', false], 'mt' => ['Malta', 'Valletta', 'EU', false],
        'cy' => ['Cyprus', 'Nicosia', 'EU', false], 'mc' => ['Monaco', 'Monaco', 'EU', false],
        // Africa
        'eg' => ['Egypt', 'Cairo', 'AF', true], 'ma' => ['Morocco', 'Rabat', 'AF', true], 'dz' => ['Algeria', 'Algiers', 'AF', false],
        'tn' => ['Tunisia', 'Tunis', 'AF', false], 'ly' => ['Libya', 'Tripoli', 'AF', false], 'sd' => ['Sudan', 'Khartoum', 'AF', false],
        'ng' => ['Nigeria', 'Abuja', 'AF', true], 'gh' => ['Ghana', 'Accra', 'AF', false], 'ke' => ['Kenya', 'Nairobi', 'AF', true],
        'et' => ['Ethiopia', 'Addis Ababa', 'AF', false], 'tz' => ['Tanzania', 'Dodoma', 'AF', false], 'ug' => ['Uganda', 'Kampala', 'AF', false],
        'rw' => ['Rwanda', 'Kigali', 'AF', false], 'so' => ['Somalia', 'Mogadishu', 'AF', false], 'za' => ['South Africa', null, 'AF', true],
        'zw' => ['Zimbabwe', 'Harare', 'AF', false], 'zm' => ['Zambia', 'Lusaka', 'AF', false], 'ao' => ['Angola', 'Luanda', 'AF', false],
        'cm' => ['Cameroon', 'Yaoundé', 'AF', false], 'sn' => ['Senegal', 'Dakar', 'AF', false], 'ml' => ['Mali', 'Bamako', 'AF', false],
        'ne' => ['Niger', 'Niamey', 'AF', false], 'td' => ['Chad', "N'Djamena", 'AF', false], 'cd' => ['DR Congo', 'Kinshasa', 'AF', false],
        'cg' => ['Republic of the Congo', 'Brazzaville', 'AF', false], 'mg' => ['Madagascar', 'Antananarivo', 'AF', false], 'mz' => ['Mozambique', 'Maputo', 'AF', false],
        'na' => ['Namibia', 'Windhoek', 'AF', false], 'bw' => ['Botswana', 'Gaborone', 'AF', false], 'mu' => ['Mauritius', 'Port Louis', 'AF', false],
        'sc' => ['Seychelles', 'Victoria', 'AF', false], 'ls' => ['Lesotho', 'Maseru', 'AF', false],
        // North America
        'us' => ['United States', 'Washington, D.C.', 'NA', true], 'ca' => ['Canada', 'Ottawa', 'NA', true], 'mx' => ['Mexico', 'Mexico City', 'NA', true],
        'cu' => ['Cuba', 'Havana', 'NA', false], 'jm' => ['Jamaica', 'Kingston', 'NA', false], 'pa' => ['Panama', 'Panama City', 'NA', false],
        'cr' => ['Costa Rica', 'San José', 'NA', false], 'gt' => ['Guatemala', 'Guatemala City', 'NA', false], 'hn' => ['Honduras', 'Tegucigalpa', 'NA', false],
        'do' => ['Dominican Republic', 'Santo Domingo', 'NA', false], 'ht' => ['Haiti', 'Port-au-Prince', 'NA', false], 'tt' => ['Trinidad and Tobago', 'Port of Spain', 'NA', false],
        'bb' => ['Barbados', 'Bridgetown', 'NA', false],
        // South America
        'br' => ['Brazil', 'Brasília', 'SA', true], 'ar' => ['Argentina', 'Buenos Aires', 'SA', true], 'cl' => ['Chile', 'Santiago', 'SA', false],
        'pe' => ['Peru', 'Lima', 'SA', false], 'co' => ['Colombia', 'Bogotá', 'SA', false], 've' => ['Venezuela', 'Caracas', 'SA', false],
        'ec' => ['Ecuador', 'Quito', 'SA', false], 'bo' => ['Bolivia', null, 'SA', false], 'py' => ['Paraguay', 'Asunción', 'SA', false],
        'uy' => ['Uruguay', 'Montevideo', 'SA', false],
        // Oceania
        'au' => ['Australia', 'Canberra', 'OC', true], 'nz' => ['New Zealand', 'Wellington', 'OC', true], 'fj' => ['Fiji', 'Suva', 'OC', false],
        'pg' => ['Papua New Guinea', 'Port Moresby', 'OC', false],
    ];

    /** Compact list for the game pages: [{c, n, cap, k, e}]. */
    public static function forJs(bool $capitalsOnly = false): array
    {
        $out = [];
        foreach (self::ALL as $code => [$name, $capital, $continent, $easy]) {
            if ($capitalsOnly && $capital === null) {
                continue;
            }
            $out[] = ['c' => $code, 'n' => $name, 'cap' => $capital, 'k' => $continent, 'e' => $easy];
        }

        return $out;
    }
}
