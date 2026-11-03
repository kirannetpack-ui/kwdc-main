<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\Equipment;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\SecurityAgency;
use App\Models\EquipmentRequest;
use App\Models\WarehouseRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SmartAIService
{
    protected $aiService;
    protected $actionPlanner;

    public function __construct(EnhancedAIService $aiService, AssistantActionPlanner $actionPlanner)
    {
        $this->aiService = $aiService;
        $this->actionPlanner = $actionPlanner;
    }

    /**
     * Process user query with full context and intelligent guidance
     */
    public function processQuery(string $userQuery, ?int $userId = null): array
    {
        $user = $userId ? User::find($userId) : null;
        $plannedAction = $this->actionPlanner->plan($userQuery, $userId, $user?->role);

        if (($plannedAction['intent'] ?? 'general_help') !== 'general_help') {
            return $this->withGuidance($plannedAction);
        }

        $lower = strtolower($userQuery);
        
        // Detect intent across all business functions
        $intent = $this->detectComprehensiveIntent($lower);
        
        if (!$intent) {
            return $this->getGeneralAssistance($userQuery, $userId);
        }

        // Build response with intelligent guidance and recommendations
        return $this->buildSmartResponse($intent, $userQuery, $userId);
    }

    /**
     * Detect comprehensive business intents
     */
    private function detectComprehensiveIntent(string $lower): ?array
    {
        // TRACKING & STATUS (Check BEFORE dispatch since "track my dispatch" contains "dispatch")
        if (preg_match('/\b(track|tracking|where is|location|status|progress|where|check)\b/', $lower) && 
            preg_match('/\b(my|dispatch|pickup|package|order|shipment)\b/', $lower)) {
            return ['category' => 'tracking', 'intent' => 'track_shipment'];
        }

        // PICKUP & COLLECTION (Check FIRST after tracking - explicit pickup keyword takes priority)
        if (preg_match('/\b(pickup|pick up|collection|collect|fetch)\b/', $lower)) {
            // Only treat as dispatch if explicitly says so, even with "cargo" mention
            if (preg_match('/\b(dispatch|create dispatch|send dispatch|delivery|shipment|trip)\b/', $lower)) {
                return ['category' => 'dispatch', 'intent' => 'dispatch_request'];
            }
            // Otherwise it's pickup
            return ['category' => 'pickup', 'intent' => 'pickup_request'];
        }

        // DISPATCH & LOGISTICS (Only if explicit pickup wasn't found and not tracking)
        if (preg_match('/\b(dispatch|delivery|shipment|trip|send|transport)\b/', $lower)) {
            if (!preg_match('/\b(equipment|machine|vehicle)\b/', $lower)) {
                return ['category' => 'dispatch', 'intent' => 'dispatch_request'];
            }
        }

        // EQUIPMENT RENTAL
        if (preg_match('/\b(equipment|machine|rent|lease|tractor|forklift|loader|excavator|jcb|crane|bulldozer)\b/', $lower)) {
            if (preg_match('/\b(rent|lease|hire|need)\b/', $lower) || preg_match('/for.*(?:day|week|month|hour)/i', $lower)) {
                return ['category' => 'equipment', 'intent' => 'equipment_rental'];
            }
        }

        // WAREHOUSE RENTAL
        if (preg_match('/\b(warehouse|storage|space|facility|store|rent|lease|room)\b/', $lower)) {
            if (preg_match('/\b(rent|lease|hire|need|looking|storage|store)\b/', $lower)) {
                return ['category' => 'warehouse', 'intent' => 'warehouse_rental'];
            }
        }

        // SECURITY SERVICES
        if (preg_match('/\b(security|guard|personnel|protection|secure|safe|escort|agency)\b/', $lower)) {
            if (preg_match('/\b(need|hire|require|book|request)\b/', $lower)) {
                return ['category' => 'security', 'intent' => 'security_booking'];
            }
        }

        // VEHICLE SERVICES
        if (preg_match('/\b(vehicle|car|truck|van|bus|bike|motorcycle|transport)\b/', $lower)) {
            if (preg_match('/\b(need|rent|hire|book|service)\b/', $lower)) {
                return ['category' => 'vehicle', 'intent' => 'vehicle_booking'];
            }
        }

        // LOADER/LABOR SERVICES
        if (preg_match('/\b(loader|labor|labour|manpower|helper|worker|team)\b/', $lower)) {
            if (preg_match('/\b(need|hire|require|book)\b/', $lower)) {
                return ['category' => 'labor', 'intent' => 'labor_request'];
            }
        }

        // ACCOUNT & AUTHENTICATION
        if (preg_match('/\b(logout|sign out|exit|quit|bye|goodbye|account)\b/', $lower)) {
            return ['category' => 'account', 'intent' => 'logout'];
        }

        // GENERAL INQUIRY
        if (preg_match('/\b(help|what|how|tell|explain|info|information|guide|assist)\b/', $lower)) {
            return ['category' => 'general', 'intent' => 'general_help'];
        }

        return null;
    }

    private function withGuidance(array $plannedAction): array
    {
        $intent = $plannedAction['intent'] ?? 'general_help';
        $data = $plannedAction['data'] ?? [];

        return array_merge([
            'done' => false,
            'requires_confirmation' => in_array($intent, ['pickup_request', 'dispatch_request', 'reminder_create'], true),
            'guidance' => [
                'title' => $this->guidanceTitle($intent),
                'description' => 'I filled what I could understand. Please review the form before saving.',
                'fields_guidance' => $this->fieldsForIntent($intent, $data),
            ],
        ], $plannedAction);
    }

    private function guidanceTitle(string $intent): string
    {
        return match ($intent) {
            'pickup_request' => 'Pickup form ready',
            'dispatch_request' => 'Dispatch form ready',
            'reminder_create' => 'Reminder ready',
            'tracking' => 'Tracking page ready',
            'invoice_lookup' => 'Invoices ready',
            'warehouse_rental' => 'Warehouse request ready',
            'equipment_rental' => 'Equipment request ready',
            'security_booking' => 'Security booking ready',
            default => 'KWDC assistant',
        };
    }

    private function fieldsForIntent(string $intent, array $data): array
    {
        $commonRoute = [
            'pickup_address' => [
                'label' => 'Pickup Location',
                'hint' => 'Where the item or cargo starts',
                'required' => true,
                'value' => $data['pickup_address'] ?? null,
            ],
            'delivery_address' => [
                'label' => 'Destination',
                'hint' => 'Where it should go',
                'required' => true,
                'value' => $data['delivery_address'] ?? null,
            ],
            'items_description' => [
                'label' => 'Goods',
                'hint' => 'Boxes, weight, or item details',
                'required' => false,
                'value' => $data['items_description'] ?? null,
            ],
        ];

        return match ($intent) {
            'pickup_request', 'dispatch_request' => $commonRoute,
            'reminder_create' => [
                'title' => [
                    'label' => 'Title',
                    'hint' => 'What to remember',
                    'required' => true,
                    'value' => $data['title'] ?? null,
                ],
                'starts_at' => [
                    'label' => 'Date and Time',
                    'hint' => 'When the reminder is due',
                    'required' => true,
                    'value' => $data['starts_at'] ?? null,
                ],
            ],
            default => [],
        };
    }

    /**
     * Build comprehensive response with guidance and recommendations
     */
    private function buildSmartResponse(array $intent, string $userQuery, ?int $userId): array
    {
        $category = $intent['category'];
        $intentType = $intent['intent'];

        $response = [
            'action' => 'guidance',
            'intent' => $intentType,
            'category' => $category,
        ];

        // Extract relevant data from query
        $extractedData = $this->extractContextualData($userQuery, $category);

        switch ($category) {
            case 'pickup':
                return $this->handlePickupRequest($userQuery, $extractedData, $userId);
            
            case 'dispatch':
                return $this->handleDispatchRequest($userQuery, $extractedData, $userId);
            
            case 'warehouse':
                return $this->handleWarehouseRequest($userQuery, $extractedData, $userId);
            
            case 'equipment':
                return $this->handleEquipmentRequest($userQuery, $extractedData, $userId);
            
            case 'security':
                return $this->handleSecurityRequest($userQuery, $extractedData, $userId);
            
            case 'vehicle':
                return $this->handleVehicleRequest($userQuery, $extractedData, $userId);
            
            case 'labor':
                return $this->handleLaborRequest($userQuery, $extractedData, $userId);
            
            case 'tracking':
                return $this->handleTrackingRequest($userQuery, $extractedData, $userId);
            
            case 'account':
                return $this->handleAccountRequest($userQuery);
            
            default:
                return $this->getGeneralAssistance($userQuery, $userId);
        }
    }

    /**
     * Handle pickup request with guidance
     */
    private function handlePickupRequest(string $query, array $extractedData, ?int $userId): array
    {
        return [
            'action' => 'open_page',
            'url' => '/pickup/direct-create',
            'intent' => 'pickup_request',
            'guidance' => [
                'title' => '📦 Pickup Service Request',
                'description' => 'I\'ll help you book a pickup service. Please provide the following information:',
                'fields_guidance' => [
                    'pickup_address' => [
                        'label' => 'Pickup Location',
                        'hint' => 'Where should we pick up the item from?',
                        'example' => 'e.g., Kathmandu, Boudha, Thamel',
                        'required' => true,
                        'value' => $extractedData['pickup_address'] ?? null,
                    ],
                    'delivery_address' => [
                        'label' => 'Delivery Location',
                        'hint' => 'Where should the item be delivered to?',
                        'example' => 'e.g., Bhaktapur, Pokhara, Lalitpur',
                        'required' => true,
                        'value' => $extractedData['delivery_address'] ?? null,
                    ],
                    'description' => [
                        'label' => 'What is being picked up?',
                        'hint' => 'Describe the item/package (size, weight, fragility)',
                        'example' => 'e.g., Box of documents, furniture, electronics',
                        'required' => true,
                        'value' => null,
                    ],
                    'phone' => [
                        'label' => 'Contact Number',
                        'hint' => 'Your phone number for the delivery driver',
                        'example' => 'e.g., 9841234567',
                        'required' => true,
                        'value' => null,
                    ],
                ],
                'next_steps' => [
                    '1. Enter pickup and delivery locations',
                    '2. Describe the item you want picked up',
                    '3. Provide your contact number',
                    '4. Choose pickup time',
                    '5. Get instant price quote',
                ],
            ],
            'message' => 'Opening pickup form. ' . ($extractedData['pickup_address'] ? 'I found "' . $extractedData['pickup_address'] . '" as your pickup location.' : 'Please enter pickup and delivery locations.'),
            'data' => $extractedData,
            'recommendations' => $this->getPickupRecommendations($userId),
        ];
    }

    /**
     * Handle dispatch request with guidance
     */
    private function handleDispatchRequest(string $query, array $extractedData, ?int $userId): array
    {
        return [
            'action' => 'open_page',
            'url' => '/dispatch/direct-create',
            'intent' => 'dispatch_request',
            'guidance' => [
                'title' => '🚚 Dispatch & Delivery Service',
                'description' => 'I\'ll help you create a dispatch order. Here\'s what I need:',
                'fields_guidance' => [
                    'pickup_address' => [
                        'label' => 'Pickup Location (Origin)',
                        'hint' => 'Where is the cargo currently located?',
                        'example' => 'e.g., Kathmandu, Birgunj, Nepaljung',
                        'required' => true,
                        'value' => $extractedData['pickup_address'] ?? null,
                    ],
                    'delivery_address' => [
                        'label' => 'Delivery Location (Destination)',
                        'hint' => 'Where should the cargo be delivered?',
                        'example' => 'e.g., Pokhara, Dharan, Ilam',
                        'required' => true,
                        'value' => $extractedData['delivery_address'] ?? null,
                    ],
                    'total_distance' => [
                        'label' => 'Distance',
                        'hint' => 'Estimated distance in kilometers',
                        'example' => 'e.g., 50 km, 100 km',
                        'required' => true,
                        'value' => $extractedData['total_distance'] ?? null,
                    ],
                    'cargo_type' => [
                        'label' => 'Type of Cargo',
                        'hint' => 'What type of items are being transported?',
                        'example' => 'e.g., Electronics, Furniture, Documents, Fragile',
                        'required' => true,
                        'value' => null,
                    ],
                    'vehicle_type' => [
                        'label' => 'Required Vehicle Type',
                        'hint' => 'Choose appropriate vehicle size',
                        'example' => 'e.g., Standard Van, Truck, Large Truck',
                        'required' => true,
                        'value' => null,
                        'options' => ['Standard Van', 'Truck', 'Large Truck', 'Flatbed'],
                    ],
                ],
                'next_steps' => [
                    '1. Enter origin and destination',
                    '2. Specify cargo type and dimensions',
                    '3. Select vehicle type',
                    '4. Add special handling instructions if needed',
                    '5. Get price quote and available drivers',
                ],
            ],
            'message' => 'Opening dispatch form. ' . ($extractedData['pickup_address'] && $extractedData['delivery_address'] ? 'Route: ' . $extractedData['pickup_address'] . ' → ' . $extractedData['delivery_address'] : 'Please enter route details.'),
            'data' => $extractedData,
            'recommendations' => $this->getDispatchRecommendations($extractedData, $userId),
        ];
    }

    /**
     * Handle warehouse rental request
     */
    private function handleWarehouseRequest(string $query, array $extractedData, ?int $userId): array
    {
        $warehouseRecommendations = $this->findRelevantWarehouses($extractedData);

        return [
            'action' => 'guidance',
            'intent' => 'warehouse_rental',
            'guidance' => [
                'title' => '🏢 Warehouse & Storage Space Rental',
                'description' => 'I can help you find the perfect warehouse space. Let me guide you:',
                'fields_guidance' => [
                    'location' => [
                        'label' => 'Preferred Location',
                        'hint' => 'Which area/city do you need warehouse space in?',
                        'example' => 'e.g., Kathmandu, Bhaktapur, Lalitpur, Pokhara',
                        'required' => true,
                        'value' => $extractedData['location'] ?? null,
                    ],
                    'required_area' => [
                        'label' => 'Required Space Size',
                        'hint' => 'How much space do you need?',
                        'example' => 'e.g., 1000 sqft, 500 sqm, 2000 sqft',
                        'required' => true,
                        'value' => $extractedData['area'] ?? null,
                    ],
                    'duration' => [
                        'label' => 'Rental Duration',
                        'hint' => 'How long do you need the space?',
                        'example' => 'e.g., 1 month, 6 months, 1 year, flexible',
                        'required' => true,
                        'value' => null,
                        'options' => ['1 Month', '3 Months', '6 Months', '1 Year', 'Flexible'],
                    ],
                    'features_needed' => [
                        'label' => 'Required Features',
                        'hint' => 'What amenities do you need?',
                        'example' => 'e.g., CCTV, Guards, Loading Dock, Climate Control',
                        'required' => false,
                        'value' => null,
                    ],
                ],
                'next_steps' => [
                    '1. Tell me your location preference',
                    '2. Specify the space size needed',
                    '3. Mention rental duration',
                    '4. I\'ll show available options',
                    '5. Compare prices and features',
                ],
            ],
            'message' => 'I found ' . count($warehouseRecommendations) . ' warehouse options matching your needs.',
            'available_warehouses' => $warehouseRecommendations,
            'url' => '/client/warehouse/request',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle equipment rental request
     */
    private function handleEquipmentRequest(string $query, array $extractedData, ?int $userId): array
    {
        $equipmentRecommendations = $this->findRelevantEquipment($extractedData);

        return [
            'action' => 'guidance',
            'intent' => 'equipment_rental',
            'guidance' => [
                'title' => '🏗️ Equipment & Machinery Rental',
                'description' => 'I\'ll help you find the right equipment for your project.',
                'fields_guidance' => [
                    'equipment_type' => [
                        'label' => 'Equipment Type',
                        'hint' => 'What type of equipment do you need?',
                        'example' => 'e.g., JCB, Crane, Forklift, Excavator, Loader',
                        'required' => true,
                        'value' => $extractedData['equipment_type'] ?? null,
                    ],
                    'purpose' => [
                        'label' => 'Purpose/Project Type',
                        'hint' => 'What is the equipment for?',
                        'example' => 'e.g., Construction, Excavation, Loading, Material Handling',
                        'required' => true,
                        'value' => $extractedData['purpose'] ?? null,
                    ],
                    'required_duration' => [
                        'label' => 'Rental Duration',
                        'hint' => 'How long do you need the equipment?',
                        'example' => 'e.g., 1 day, 1 week, 1 month',
                        'required' => true,
                        'value' => $extractedData['duration'] ?? null,
                    ],
                    'location' => [
                        'label' => 'Delivery Location',
                        'hint' => 'Where do you need the equipment delivered?',
                        'example' => 'e.g., Kathmandu, Bhaktapur, Pokhara',
                        'required' => true,
                        'value' => $extractedData['location'] ?? null,
                    ],
                    'operator_needed' => [
                        'label' => 'Do you need an operator?',
                        'hint' => 'Should we provide a trained operator?',
                        'required' => false,
                        'value' => null,
                        'options' => ['Yes, with operator', 'No, self-operated'],
                    ],
                ],
                'next_steps' => [
                    '1. Select equipment type',
                    '2. Tell me the project purpose',
                    '3. Specify rental duration',
                    '4. Provide delivery location',
                    '5. I\'ll show available options with prices',
                ],
            ],
            'message' => 'I found ' . count($equipmentRecommendations) . ' equipment options available.',
            'available_equipment' => $equipmentRecommendations,
            'url' => '/client/equipment/request',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle security services request
     */
    private function handleSecurityRequest(string $query, array $extractedData, ?int $userId): array
    {
        $securityOptions = $this->findRelevantSecurityServices($extractedData);

        return [
            'action' => 'guidance',
            'intent' => 'security_booking',
            'guidance' => [
                'title' => '🔐 Professional Security Services',
                'description' => 'I\'ll help you arrange professional security personnel.',
                'fields_guidance' => [
                    'security_type' => [
                        'label' => 'Type of Security Needed',
                        'hint' => 'What kind of security do you need?',
                        'example' => 'e.g., Site Security, Event Security, Goods Protection, Escort',
                        'required' => true,
                        'value' => $extractedData['security_type'] ?? null,
                        'options' => ['Site Security', 'Event Security', 'Goods Security', 'Escort Service', 'Other'],
                    ],
                    'location' => [
                        'label' => 'Location',
                        'hint' => 'Where do you need security personnel?',
                        'example' => 'e.g., Kathmandu, Bhaktapur, Lalitpur',
                        'required' => true,
                        'value' => $extractedData['location'] ?? null,
                    ],
                    'number_of_personnel' => [
                        'label' => 'Number of Personnel Needed',
                        'hint' => 'How many security personnel do you need?',
                        'example' => 'e.g., 2, 5, 10',
                        'required' => true,
                        'value' => $extractedData['count'] ?? null,
                    ],
                    'duration' => [
                        'label' => 'Required Duration',
                        'hint' => 'How long do you need the service?',
                        'example' => 'e.g., 1 day, 1 week, ongoing',
                        'required' => true,
                        'value' => $extractedData['duration'] ?? null,
                    ],
                ],
                'next_steps' => [
                    '1. Select type of security service',
                    '2. Specify location and number of personnel',
                    '3. Mention duration (hours/days/ongoing)',
                    '4. Review available agencies',
                    '5. Get quote and book service',
                ],
            ],
            'message' => 'I found ' . count($securityOptions) . ' security agencies that can help.',
            'available_agencies' => $securityOptions,
            'url' => '/client/security-booking',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle vehicle booking request
     */
    private function handleVehicleRequest(string $query, array $extractedData, ?int $userId): array
    {
        return [
            'action' => 'guidance',
            'intent' => 'vehicle_booking',
            'guidance' => [
                'title' => '🚗 Vehicle Rental & Transportation Services',
                'description' => 'I\'ll help you find the right vehicle for your needs.',
                'fields_guidance' => [
                    'vehicle_type' => [
                        'label' => 'Vehicle Type',
                        'hint' => 'What type of vehicle do you need?',
                        'example' => 'e.g., Car, Van, Truck, Bus, Motorcycle',
                        'required' => true,
                        'value' => $extractedData['vehicle_type'] ?? null,
                    ],
                    'purpose' => [
                        'label' => 'Purpose',
                        'hint' => 'What do you need the vehicle for?',
                        'example' => 'e.g., Transportation, Delivery, Tour, Personal Use',
                        'required' => true,
                        'value' => null,
                    ],
                    'rental_period' => [
                        'label' => 'Rental Period',
                        'hint' => 'How long do you need the vehicle?',
                        'example' => 'e.g., 1 day, 3 days, 1 week, 1 month',
                        'required' => true,
                        'value' => $extractedData['duration'] ?? null,
                    ],
                ],
                'next_steps' => [
                    '1. Select vehicle type',
                    '2. Specify usage purpose',
                    '3. Choose rental period',
                    '4. View available vehicles',
                    '5. Book and confirm reservation',
                ],
            ],
            'message' => 'Let me help you find the perfect vehicle.',
            'url' => '/vehicles/book',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle labor/loader request
     */
    private function handleLaborRequest(string $query, array $extractedData, ?int $userId): array
    {
        return [
            'action' => 'guidance',
            'intent' => 'labor_request',
            'guidance' => [
                'title' => '👷 Loader & Labor Services',
                'description' => 'I\'ll help you arrange skilled loaders and labor for your project.',
                'fields_guidance' => [
                    'job_type' => [
                        'label' => 'Type of Work',
                        'hint' => 'What type of work do you need help with?',
                        'example' => 'e.g., Loading/Unloading, Shifting, Construction, Heavy Lifting',
                        'required' => true,
                        'value' => $extractedData['job_type'] ?? null,
                    ],
                    'number_of_workers' => [
                        'label' => 'Number of Workers Needed',
                        'hint' => 'How many workers do you need?',
                        'example' => 'e.g., 2, 5, 10, 20',
                        'required' => true,
                        'value' => $extractedData['count'] ?? null,
                    ],
                    'location' => [
                        'label' => 'Job Location',
                        'hint' => 'Where is the work location?',
                        'example' => 'e.g., Kathmandu, Bhaktapur',
                        'required' => true,
                        'value' => $extractedData['location'] ?? null,
                    ],
                    'required_hours' => [
                        'label' => 'Required Duration',
                        'hint' => 'How many hours/days do you need the workers?',
                        'example' => 'e.g., 4 hours, 1 day, 3 days',
                        'required' => true,
                        'value' => $extractedData['duration'] ?? null,
                    ],
                ],
                'next_steps' => [
                    '1. Describe type of work needed',
                    '2. Specify number of workers',
                    '3. Mention job location',
                    '4. Specify duration',
                    '5. Get quotes from labor managers',
                ],
            ],
            'message' => 'I\'ll help you find skilled workers for your project.',
            'url' => '/loaders/request',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle tracking request
     */
    private function handleTrackingRequest(string $query, array $extractedData, ?int $userId): array
    {
        return [
            'action' => 'open_page',
            'url' => '/dispatch',
            'intent' => 'track_shipment',
            'guidance' => [
                'title' => '📍 Track Your Shipment',
                'description' => 'I can help you track your current and past shipments.',
                'options' => [
                    'Track by Reference' => 'Enter your dispatch reference number',
                    'View Recent Orders' => 'See your last 5 shipments',
                    'Track by Phone' => 'Provide phone number used in the order',
                ],
            ],
            'message' => 'Opening tracking page. You can search by reference number or phone.',
            'data' => $extractedData,
        ];
    }

    /**
     * Handle account-related requests
     */
    private function handleAccountRequest(string $query): array
    {
        return [
            'action' => 'logout',
            'intent' => 'logout',
            'message' => 'Logging you out. Thank you for using KTM-WDC services!',
        ];
    }

    /**
     * Extract contextual data from user query
     */
    private function extractContextualData(string $query, string $category): array
    {
        $data = [];
        $clean = trim($query);

        // Location extraction
        if (preg_match('/(?:from|at|in|kathmandu|bhaktapur|lalitpur|pokhara|kirtipur|thimi)\s+([A-Za-z0-9À-ÿ,\-\/\s.]+?)(?=\s+(?:to|for|with|,|$))/i', $clean, $matches)) {
            $data['location'] = trim($matches[1]);
        }

        // Addresses
        if (preg_match('/(?:pickup|pick up|from)\s+([A-Za-z0-9À-ÿ,\-\/\s.]+?)(?=\s+(?:to|drop|deliver|,|$))/i', $clean, $matches)) {
            $data['pickup_address'] = trim($matches[1]);
        }

        if (preg_match('/(?:drop(?:ped)?|deliver(?:y)?|to)\s+([A-Za-z0-9À-ÿ,\-\/\s.]+?)(?=\s+(?:with|for|,|$)|,|$)/i', $clean, $matches)) {
            $data['delivery_address'] = trim($matches[1]);
        }

        // Area/Size extraction
        if (preg_match('/(\d+(?:,\d+)*)\s*(?:sqft|sqm|square|sq\.?)\b/i', $clean, $matches)) {
            $data['area'] = trim($matches[1]);
        }

        // Duration extraction
        if (preg_match('/(?:for|duration|need)\s+(\d+)\s+(hour|day|week|month|year)/i', $clean, $matches)) {
            $data['duration'] = $matches[1] . ' ' . $matches[2];
        }

        // Distance extraction
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:km|kilometer)/i', $clean, $matches)) {
            $data['total_distance'] = trim($matches[0]);
        }

        // Price/Budget extraction
        if (preg_match('/(?:budget|price|cost|amount|rs|रू|nrs)\s*(?:of\s+)?(?:रू|rs|nrs)?\s*(\d+(?:,\d+)*)/i', $clean, $matches)) {
            $data['budget'] = str_replace(',', '', $matches[1]);
        }

        // Equipment type
        if (preg_match('/(?:need|rent|lease)\s+(jcb|forklift|crane|loader|excavator|bulldozer|truck)/i', $clean, $matches)) {
            $data['equipment_type'] = ucfirst($matches[1]);
        }

        // Count/Quantity
        if (preg_match('/(\d+)\s+(?:person|personnel|worker|unit|item)/i', $clean, $matches)) {
            $data['count'] = $matches[1];
        }

        return array_filter($data);
    }

    /**
     * Get pickup service recommendations
     */
    private function getPickupRecommendations(?int $userId): array
    {
        return [
            'tips' => [
                '✓ Make sure the package is properly packed',
                '✓ Provide accurate pickup address',
                '✓ Have contact number ready for driver',
                '✓ Be available at pickup time',
            ],
            'typical_price_range' => '₹300 - ₹1000 depending on distance',
            'service_time' => 'Usually same day or next day pickup available',
        ];
    }

    /**
     * Get dispatch recommendations based on extracted data
     */
    private function getDispatchRecommendations(array $extractedData, ?int $userId): array
    {
        $recommendations = [
            'tips' => [
                '✓ Provide exact pickup and delivery addresses',
                '✓ Specify cargo type for proper vehicle selection',
                '✓ Insurance recommended for high-value items',
                '✓ Confirm delivery time window',
            ],
        ];

        // Add distance-based recommendations
        if (isset($extractedData['total_distance'])) {
            if (preg_match('/(\d+)/', $extractedData['total_distance'], $matches)) {
                $distance = (int)$matches[1];
                if ($distance < 20) {
                    $recommendations['suggested_vehicle'] = 'Standard Van or Motorcycle';
                    $recommendations['estimated_time'] = '30-60 minutes';
                } elseif ($distance < 50) {
                    $recommendations['suggested_vehicle'] = 'Standard Truck';
                    $recommendations['estimated_time'] = '2-3 hours';
                } else {
                    $recommendations['suggested_vehicle'] = 'Large Truck or Flatbed';
                    $recommendations['estimated_time'] = '4+ hours';
                }
            }
        }

        return $recommendations;
    }

    /**
     * Find relevant warehouses based on criteria
     */
    private function findRelevantWarehouses(array $criteria): array
    {
        $query = Warehouse::where('status', 'active');

        // Filter by location if specified
        if (isset($criteria['location'])) {
            $query->where('city', 'like', '%' . $criteria['location'] . '%')
                  ->orWhere('address', 'like', '%' . $criteria['location'] . '%');
        }

        // Filter by area if specified
        if (isset($criteria['area'])) {
            if (preg_match('/(\d+)/', $criteria['area'], $matches)) {
                $minArea = (int)$matches[1];
                $query->where('area_sqft', '>=', $minArea);
            }
        }

        $warehouses = $query->limit(5)->get(['id', 'name', 'location', 'area_sqft', 'price', 'cctv_count', 'guards_count']);

        return $warehouses->map(function($w) {
            return [
                'id' => $w->id,
                'name' => $w->name,
                'location' => $w->location,
                'size' => $w->area_sqft . ' sqft',
                'price' => '₹' . number_format($w->price),
                'features' => implode(', ', array_filter([
                    $w->cctv_count ? $w->cctv_count . ' CCTV cameras' : null,
                    $w->guards_count ? $w->guards_count . ' Guards' : null,
                ])),
            ];
        })->toArray();
    }

    /**
     * Find relevant equipment based on criteria
     */
    private function findRelevantEquipment(array $criteria): array
    {
        $query = Equipment::where('status', 'active');

        // Filter by type if specified
        if (isset($criteria['equipment_type'])) {
            $query->where('type', 'like', '%' . $criteria['equipment_type'] . '%');
        }

        $equipment = $query->limit(5)->get(['id', 'type', 'model', 'hourly_rate', 'daily_rate', 'availability']);

        return $equipment->map(function($e) {
            return [
                'id' => $e->id,
                'type' => $e->type,
                'model' => $e->model,
                'hourly_rate' => $e->hourly_rate ? '₹' . $e->hourly_rate . '/hour' : null,
                'daily_rate' => $e->daily_rate ? '₹' . $e->daily_rate . '/day' : null,
                'status' => $e->availability ?? 'Available',
            ];
        })->toArray();
    }

    /**
     * Find relevant security services
     */
    private function findRelevantSecurityServices(array $criteria): array
    {
        $query = SecurityAgency::where('status', 'active');

        // Filter by location if specified
        if (isset($criteria['location'])) {
            $query->where('location', 'like', '%' . $criteria['location'] . '%')
                  ->orWhere('service_area', 'like', '%' . $criteria['location'] . '%');
        }

        $agencies = $query->limit(5)->get(['id', 'name', 'location', 'personnel_count', 'hourly_rate', 'license_number']);

        return $agencies->map(function($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'location' => $a->location,
                'available_personnel' => $a->personnel_count,
                'rate' => '₹' . number_format($a->hourly_rate) . '/hour',
                'licensed' => $a->license_number ? '✓ Licensed' : '⚠ Check License',
            ];
        })->toArray();
    }

    /**
     * Provide general AI assistance
     */
    public function getGeneralAssistance(string $query, ?int $userId): array
    {
        if (!app()->environment('testing') && (config('services.gemini.api_key') || config('services.openai.api_key'))) {
            try {
                $systemPrompt = <<<SYS
You are the KTM-WDC Logistics Copilot, an AI assistant for warehouse management, parcel pickups, cargo dispatches, and logistics in Kathmandu Valley and across Nepal.
Provide friendly, helpful, and concise answers (2-3 sentences max).
If the user is asking about an operational action (e.g. sending cargo, booking warehouse space, scheduling a pickup), tell them how you can help or guide them to the right form.
Support both English and Nepali naturally.
SYS;
                $aiReply = $this->aiService->chat($systemPrompt, $query, 'text');
                if ($aiReply && is_string($aiReply) && trim($aiReply) !== '') {
                    return [
                        'action' => 'general_help',
                        'title' => 'KWDC AI Assistant',
                        'message' => trim($aiReply),
                        'done' => false,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini general assistance failed, falling back to static menu: ' . $e->getMessage());
            }
        }

        return [
            'action' => 'general_help',
            'title' => '🤖 KTM-WDC AI Assistant',
            'message' => 'I can help you with:',
            'services' => [
                '📦 Pickup Services' => 'Pick up items and deliver them',
                '🚚 Dispatch Services' => 'Send cargo to other cities',
                '📍 Track Shipments' => 'Check status of your orders',
                '🏢 Warehouse Rental' => 'Find storage space',
                '🏗️ Equipment Rental' => 'Rent machinery and equipment',
                '🔐 Security Services' => 'Professional security personnel',
                '🚗 Vehicle Rental' => 'Rent vehicles for transportation',
                '👷 Labor Services' => 'Hire workers for your project',
            ],
            'example_queries' => [
                'I need pickup from Boudha to Bhaktapur',
                'Send cargo from Kathmandu to Pokhara',
                'Where is my dispatch?',
                'I need warehouse space in Bhaktapur',
                'Rent a JCB for 3 days',
                'I need 5 security guards',
            ],
            'guidance_hint' => 'Just tell me what you need and I\'ll guide you through the process!',
        ];
    }
}
