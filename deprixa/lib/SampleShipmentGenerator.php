<?php
/**
 * Realistic Sample Shipment Generator
 * ------------------------------------
 * Produces believable random shipment data for testing and demo purposes.
 *
 * Usage:
 *   $gen = new SampleShipmentGenerator();
 *   $shipmentData = $gen->generate();
 *   $trackingNumber = $gen->generateTrackingNumber();
 */

require_once __DIR__ . '/../includes/tracking.php';
require_once __DIR__ . '/../includes/validation.php';

class SampleShipmentGenerator
{
    private $db;
    private $usedTrackingNumbers = [];

    private $firstNames = [
        'James','Mary','John','Patricia','Robert','Jennifer','Michael','Linda','David','Elizabeth',
        'William','Barbara','Richard','Susan','Joseph','Jessica','Thomas','Sarah','Christopher','Karen',
        'Charles','Lisa','Daniel','Nancy','Matthew','Betty','Anthony','Margaret','Mark','Sandra',
        'Donald','Ashley','Steven','Dorothy','Andrew','Kimberly','Paul','Emily','Joshua','Donna',
        'Kenneth','Michelle','Kevin','Carol','Brian','Amanda','George','Melissa','Timothy','Deborah',
        'Alice','Bob','Carolyn','Diane','Edward','Frances','Gregory','Helen','Ivan','Julia',
    ];

    private $lastNames = [
        'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez',
        'Hernandez','Lopez','Gonzalez','Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin',
        'Lee','Perez','Thompson','White','Harris','Sanchez','Clark','Ramirez','Lewis','Robinson',
        'Walker','Young','Allen','King','Wright','Scott','Torres','Nguyen','Hill','Flores',
        'Green','Adams','Nelson','Baker','Hall','Rivera','Campbell','Mitchell','Carter','Roberts',
        'Chen','Wang','Patel','Kim','Singh','Nguyen','Tanaka','Muller','Schmidt','Fischer',
    ];

    private $companies = [
        'TechFlow Inc.','GlobalTrade Ltd.','PeakGoods Co.','SwiftLogic LLC','NorthStar Trading',
        'Oceanic Imports','PrimeExports Co.','BrightPath Logistics','Apex Distribution','CoreBridge Trading',
        'NexGen Supplies','EverGreen Imports','Summit Freight Co.','BlueWave Shipping','RedCargo Ltd.',
        'SilverLine Trading','Golden Gate Imports','Pacific Rim Trading','Atlas Logistics','Zenith Exports',
        'Quantum Goods Ltd.','Stellar Shipping Co.','Horizon Trading Co.','Pinnacle Logistics','Vanguard Imports',
    ];

    private $streetTypes = ['St','Ave','Blvd','Rd','Dr','Ln','Way','Ct','Pl','Terrace'];
    private $streetNames = [
        'Main','Oak','Pine','Maple','Cedar','Elm','Washington','Park','Lake','Hill',
        'Broadway','Market','Commerce','Industrial','Harbor','Front','River','Spring','Forest','Meadow',
        'Sunset','Sunrise','Valley','Ridge','Creek','Bay','Port','Dock','Terminal','Freight',
    ];

    private $cities = [
        'New York'=>'US','Los Angeles'=>'US','Chicago'=>'US','Houston'=>'US','Phoenix'=>'US',
        'Philadelphia'=>'US','San Antonio'=>'US','San Diego'=>'US','Dallas'=>'US','San Jose'=>'US',
        'Austin'=>'US','Jacksonville'=>'US','Fort Worth'=>'US','Columbus'=>'US','Charlotte'=>'US',
        'Indianapolis'=>'US','Seattle'=>'US','Denver'=>'US','Boston'=>'US','Miami'=>'US',
        'Atlanta'=>'US','Washington'=>'US','Long Beach'=>'US','London'=>'GB','Manchester'=>'GB',
        'Birmingham'=>'GB','Glasgow'=>'GB','Leeds'=>'GB','Liverpool'=>'GB','Bristol'=>'GB',
        'Hamburg'=>'DE','Frankfurt'=>'DE','Munich'=>'DE','Berlin'=>'DE','Cologne'=>'DE',
        'Dubai'=>'AE','Abu Dhabi'=>'AE','Sharjah'=>'AE','Shanghai'=>'CN','Shenzhen'=>'CN',
        'Beijing'=>'CN','Guangzhou'=>'CN','Hangzhou'=>'CN','Chengdu'=>'CN','Tokyo'=>'JP',
        'Osaka'=>'JP','Yokohama'=>'JP','Singapore'=>'SG','Sydney'=>'AU','Melbourne'=>'AU',
        'Toronto'=>'CA','Vancouver'=>'CA','Montreal'=>'CA','Mexico City'=>'MX','Mumbai'=>'IN',
        'Delhi'=>'IN','Bangalore'=>'IN','São Paulo'=>'BR','Rio de Janeiro'=>'BR','Cairo'=>'EG',
        'Lagos'=>'NG','Nairobi'=>'KE','Johannesburg'=>'ZA','Paris'=>'FR','Lyon'=>'FR',
        'Marseille'=>'FR','Rome'=>'IT','Milan'=>'IT','Naples'=>'IT','Madrid'=>'ES','Barcelona'=>'ES',
        'Amsterdam'=>'NL','Rotterdam'=>'NL','Zurich'=>'CH','Geneva'=>'CH','Vienna'=>'AT',
        'Warsaw'=>'PL','Prague'=>'CZ','Budapest'=>'HU','Bangkok'=>'TH','Kuala Lumpur'=>'MY',
        'Jakarta'=>'ID','Manila'=>'PH','Ho Chi Minh City'=>'VN','Taipei'=>'TW','Hong Kong'=>'HK',
    ];

    private $serviceTypes = ['standard','express','overnight','economy','same_day'];
    private $priorities = ['standard','standard','standard','high','express','low'];
    private $paymentMethods = ['credit_card','bank_transfer','cash','paypal','stripe','mobile_money'];
    private $paymentStatuses = ['paid','paid','paid','pending','partial'];
    private $parcelTypes = ['parcel','document','freight','express','international'];
    private $itemCategories = [
        'Electronics','Documents','Clothing','Books','Food & Perishables','Medicine',
        'Machinery Parts','Furniture','Cosmetics','Automotive Parts','Toys','Sports Equipment',
        'Jewelry','Artwork','Musical Instruments','Pharmaceuticals','Textiles','Ceramics',
        'Glassware','Batteries','Lithium Cells','LED Displays','Circuit Boards','Solar Panels',
    ];
    private $statuses = [
        'pending_pickup','pending_pickup','created','processing',
        'picked_up','in_transit','at_hub','out_for_delivery','delivered',
    ];

    public function __construct(PDO $db = null) {
        if ($db) {
            $this->db = $db;
            $this->loadUsedTrackingNumbers();
        }
    }

    private function loadUsedTrackingNumbers() {
        if (!$this->db) return;
        try {
            $stmt = $this->db->query("SELECT tracking_number FROM shipments WHERE tracking_number LIKE 'ASC%' OR tracking_number LIKE 'SIM-%' OR tracking_number LIKE 'TRK-%' OR tracking_number LIKE 'SHP-%' OR tracking_number LIKE 'CRX-%' OR tracking_number LIKE 'LX-%' OR tracking_number LIKE 'PKG-%' OR tracking_number LIKE 'SMP-%'");
            $this->usedTrackingNumbers = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Exception $e) { /* ignore */ }
    }

    public function generateTrackingNumber(): string {
        $maxAttempts = 20;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $num = str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
            $tn = 'ASC' . $num;
            if (!isset($this->usedTrackingNumbers[$tn])) {
                $this->usedTrackingNumbers[$tn] = true;
                return $tn;
            }
        }
        return 'ASC' . str_pad((string)random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
    }

    public function generateReceiptNumber(): string {
        return 'REC-' . str_pad((string)random_int(100000, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function randomItem(array $arr) {
        return $arr[array_rand($arr)];
    }

    private function randomItems(array $arr, int $count): array {
        $keys = array_rand($arr, min($count, count($arr)));
        if (!is_array($keys)) $keys = [$keys];
        $result = [];
        foreach ($keys as $k) $result[] = $arr[$k];
        shuffle($result);
        return $result;
    }

    private function randomPhone(string $countryCode = 'US'): string {
        $formats = [
            'US' => ['+1-###-###-####','(###) ###-####','1-###-###-####'],
            'GB' => ['+44 #### ######','(0####) ######'],
            'DE' => ['+49 ### #######','+49 (0) ### #######'],
            'AE' => ['+971 ## ### ####'],
            'CN' => ['+86 ### #### ####'],
            'JP' => ['+81-##-####-####'],
            'SG' => ['+65 #### ####'],
            'AU' => ['+61 # #### ####'],
            'IN' => ['+91-#####-#####'],
        ];
        $format = $this->randomItem($formats[$countryCode] ?? $formats['US']);
        $phone = preg_replace_callback('/[#]+/', function() { return random_int(0,9); }, $format);
        return $phone;
    }

    private function generateAddress(string $city, string $country): array {
        $streetNum = random_int(100, 9999);
        $street = $this->randomItem($this->streetNames) . ' ' . $this->randomItem($this->streetTypes);
        $zipCodes = [
            'US' => str_pad((string)random_int(10000, 99999), 5, '0', STR_PAD_LEFT),
            'GB' => strtoupper(substr(md5(uniqid()), 0, 2)) . ' ' . str_pad((string)random_int(0, 9), 1) . ' ' . str_pad((string)random_int(0, 99), 2, '0', STR_PAD_LEFT),
            'DE' => str_pad((string)random_int(10000, 99999), 5, '0', STR_PAD_LEFT),
            'AE' => str_pad((string)random_int(10000, 99999), 5, '0', STR_PAD_LEFT),
            'CN' => str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'JP' => '###-####',
            'SG' => str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'AU' => str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'IN' => str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
        ];
        $postal = $zipCodes[$country] ?? str_pad((string)random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        if ($country === 'GB') {
            $postal = strtoupper(substr(md5(uniqid()), 0, 2)) . ' ' . random_int(1, 9) . ' ' . str_pad((string)random_int(0, 99), 2, '0', STR_PAD_LEFT) . ' ' . strtoupper(substr(md5(uniqid()), 0, 2));
        }
        if ($country === 'JP') {
            $postal = str_pad((string)random_int(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        return [
            'address' => "$streetNum $street",
            'city' => $city,
            'state' => '',
            'postal' => $postal,
            'country' => $country,
        ];
    }

    private function generateEmail(string $firstName, string $lastName, string $company = ''): string {
        $domains = ['gmail.com','yahoo.com','outlook.com','hotmail.com','company.com','corp.net','mail.com','proton.me'];
        $domain = $company !== '' ? strtolower(preg_replace('/[^a-z0-9]/', '', $company)) . '.com' : $this->randomItem($domains);
        $local = strtolower($firstName[0] . $lastName . random_int(1, 99));
        return $local . '@' . $domain;
    }

    public function generate(int $count = 1): array {
        $shipments = [];
        for ($i = 0; $i < $count; $i++) {
            $shipments[] = $this->generateSingle();
        }
        return $shipments;
    }

    public function generateSingle(): array {
        $originCity = $this->randomItem(array_keys($this->cities));
        $originCountry = $this->cities[$originCity];
        $destCity = $originCity;
        $destCountry = $originCountry;
        while ($destCity === $originCity) {
            $destCity = $this->randomItem(array_keys($this->cities));
            $destCountry = $this->cities[$destCity];
        }
        $isInternational = ($originCountry !== $destCountry);

        $senderFirstName = $this->randomItem($this->firstNames);
        $senderLastName = $this->randomItem($this->lastNames);
        $senderCompany = $this->randomItem($this->companies);
        $senderAddr = $this->generateAddress($originCity, $originCountry);
        $senderPhone = $this->randomPhone($originCountry);

        $receiverFirstName = $this->randomItem($this->firstNames);
        $receiverLastName = $this->randomItem($this->lastNames);
        $receiverCompany = $this->randomItem($this->companies);
        $receiverAddr = $this->generateAddress($destCity, $destCountry);
        $receiverPhone = $this->randomPhone($destCountry);

        $weight = round(random_int(5, 50000) / 100, 2);
        $length = round(random_int(5, 120), 1);
        $width = round(random_int(5, 80), 1);
        $height = round(random_int(5, 60), 1);
        $volume = round(($length * $width * $height) / 5000, 3);
        $pieces = random_int(1, 10);
        $itemCategory = $this->randomItem($this->itemCategories);
        $isFragile = random_int(0, 5) === 0 ? 1 : 0;
        $isHazardous = random_int(0, 20) === 0 ? 1 : 0;
        $isInsured = $weight > 50 || random_int(0, 3) === 0 ? 1 : 0;
        $declaredValue = $isInsured ? round(random_int(100, 50000), 2) : 0;
        $insuranceAmount = $isInsured ? round($declaredValue * 0.05, 2) : 0;

        $serviceType = $this->randomItem($this->serviceTypes);
        $priority = $this->randomItem($this->priorities);
        $paymentMethod = $this->randomItem($this->paymentMethods);
        $paymentStatus = $this->randomItem($this->paymentStatuses);
        $parcelType = $isInternational ? 'international' : ($this->randomItem($this->parcelTypes));

        $baseCost = random_int(15, 80);
        $weightCharge = round($weight * random_int(1, 5), 2);
        $shippingCost = $baseCost + $weightCharge;
        $taxAmount = round($shippingCost * 0.08, 2);
        $discount = random_int(0, 10) === 0 ? round($shippingCost * 0.1, 2) : 0;
        $totalAmount = round($shippingCost + $taxAmount + $insuranceAmount - $discount, 2);

        $shipDate = new DateTime('-' . random_int(0, 14) . ' days');
        $shipDateStr = $shipDate->format('Y-m-d');
        $transitDays = $isInternational ? random_int(5, 21) : random_int(1, 7);
        $estDelivery = (clone $shipDate)->modify('+' . $transitDays . ' days')->format('Y-m-d');

        $status = $this->randomItem($this->statuses);
        if ($status === 'delivered') {
            $actualDelivery = (clone $shipDate)->modify('+' . random_int(1, $transitDays) . ' days')->format('Y-m-d H:i:s');
        } else {
            $actualDelivery = null;
        }

        $trackingNumber = $this->generateTrackingNumber();
        $receiptNumber = $this->generateReceiptNumber();

        $currentCity = $originCity;
        if (in_array($status, ['in_transit','at_hub','out_for_delivery','delivered'], true)) {
            $routeCities = [$originCity];
            $hops = random_int(1, 3);
            $allCities = array_keys($this->cities);
            for ($h = 0; $h < $hops; $h++) {
                $next = $this->randomItem($allCities);
                if ($next !== $routeCities[count($routeCities) - 1]) {
                    $routeCities[] = $next;
                }
            }
            $routeCities[] = $destCity;
            $currentCity = $routeCities[count($routeCities) - 1];
            if ($status === 'delivered') {
                $currentCity = $destCity;
            }
        }

        return [
            'tracking_number' => $trackingNumber,
            'receipt_number' => $receiptNumber,
            'reference_number' => 'REF-' . substr(md5(uniqid()), 0, 8),
            'status' => $status,
            'service_type' => $serviceType,
            'priority' => $priority,
            'origin_country' => $originCountry,
            'origin_city' => $originCity,
            'destination_country' => $destCountry,
            'destination_city' => $destCity,
            'current_city' => $currentCity,
            'current_country' => $status === 'delivered' ? $destCountry : $originCountry,
            'total_weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'volumetric_weight' => $volume,
            'pieces' => $pieces,
            'declared_value' => $declaredValue,
            'insurance_amount' => $insuranceAmount,
            'is_fragile' => $isFragile,
            'is_hazardous' => $isHazardous,
            'is_insured' => $isInsured,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'total_amount' => $totalAmount,
            'currency' => 'USD',
            'shipment_date' => $shipDateStr,
            'estimated_delivery' => $estDelivery,
            'actual_delivery' => $actualDelivery,
            'sender_name' => $senderFirstName . ' ' . $senderLastName,
            'sender_company' => $senderCompany,
            'sender_phone' => $senderPhone,
            'sender_email' => $this->generateEmail($senderFirstName, $senderLastName, $senderCompany),
            'sender_address' => $senderAddr['address'],
            'sender_city' => $senderAddr['city'],
            'sender_state' => $senderAddr['state'],
            'sender_postal' => $senderAddr['postal'],
            'sender_country' => $senderAddr['country'],
            'receiver_name' => $receiverFirstName . ' ' . $receiverLastName,
            'receiver_company' => $receiverCompany,
            'receiver_phone' => $receiverPhone,
            'receiver_email' => $this->generateEmail($receiverFirstName, $receiverLastName, $receiverCompany),
            'receiver_address' => $receiverAddr['address'],
            'receiver_city' => $receiverAddr['city'],
            'receiver_state' => $receiverAddr['state'],
            'receiver_postal' => $receiverAddr['postal'],
            'receiver_country' => $receiverAddr['country'],
            'package_name' => $itemCategory . ' Shipment',
            'package_description' => $itemCategory . ' - ' . ($isHazardous ? 'Hazardous materials, ' : '') . ($isFragile ? 'Fragile, ' : '') . 'Standard handling',
            'item_category' => $itemCategory,
            'shipment_type' => $parcelType,
            'notes' => 'Sample shipment generated for testing.',
            'special_instructions' => $isFragile ? 'Handle with care - Fragile' : '',
            'internal_notes' => 'Auto-generated sample data.',
            'customer_notes' => '',
            'is_active' => 1,
        ];
    }
}
