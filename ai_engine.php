<?php
// LandVision AI Smart Planning Engine - Enhanced Feasibility Version
// Runs offline using a transparent rule-based AI engine. Later you can replace this with OpenAI/Gemini API.

function generateLandPlan(array $data): array
{
    $size = max(1, (float)($data['land_size_perches'] ?? 10));
    $budget = max(0, (float)($data['budget'] ?? 0));
    $type = $data['land_type'] ?? 'general';
    $district = trim($data['district'] ?? 'Sri Lanka');
    $hasRoad = !empty($data['has_road_access']);
    $hasRiver = !empty($data['has_river']);
    $hasOldHouse = !empty($data['has_old_house']);
    $target = trim($data['target_customer'] ?? 'local customers and visitors');
    $notesRaw = $data['notes'] ?? '';
    $notes = strtolower($notesRaw);
    $expectedBusiness = extractNoteValue($notesRaw, 'Expected Business Type');
    $competitorsRaw = extractNoteValue($notesRaw, 'Nearby Competitors');
    $mapLink = extractNoteValue($notesRaw, 'Google Map Location');
    $landShape = extractNoteValue($notesRaw, 'Land Shape');

    $ideas = buildIdeaScores($type, $size, $hasRoad, $hasRiver, $hasOldHouse, $notes);
    uasort($ideas, fn($a, $b) => $b['score'] <=> $a['score']);
    $ideas = array_values($ideas);
    $best = $ideas[0] ?? ['name' => 'Multi-purpose Small Business Hub', 'score' => 50, 'reasons' => ['Balanced plan for the given land details.']];

    $tier = budgetTier($budget);
    $costs = buildCostPlan($best['name'], $budget, $size, $hasOldHouse, $hasRiver);
    $income = estimateIncome($best['name'], $budget, $size, $district);
    $layout = buildLayout($best['name'], $type, $size, $hasRoad, $hasRiver, $hasOldHouse);
    $risks = buildRisks($type, $hasRoad, $hasRiver, $hasOldHouse, $budget, $size);
    $riskHeatmap = buildRiskHeatmap($type, $hasRoad, $hasRiver, $hasOldHouse, $budget, $size);
    $scores = buildPotentialScores($type, $size, $budget, $hasRoad, $hasRiver, $hasOldHouse, $risks, $income);
    $phases = buildPhases($tier, $best['name']);
    $marketing = buildMarketing($best['name'], $district, $target);
    $comparison = buildBusinessComparison($ideas, $best['name']);
    $roi = buildRoiPlan($budget, $costs, $income);
    $packages = buildPackages($best['name'], $district);
    $facilities = buildFacilitiesChecklist($best['name'], $hasRiver, $hasOldHouse);
    $legal = buildLegalChecklist($best['name']);
    $swot = buildSwot($best['name'], $type, $hasRoad, $hasRiver, $hasOldHouse, $budget);
    $breakEven = buildBreakEven($best['name'], $income, $budget);
    $layoutDiagram = buildLayoutDiagram($best['name'], $hasRoad, $hasRiver, $hasOldHouse);
    $apiMode = buildRealAiApiSuggestion($notesRaw);
    $mapAnalysis = buildMapLocationAnalysis($district, $mapLink, $type, $hasRoad, $hasRiver);
    $imageAnalysis = buildImageAiAnalysis($type, $hasRoad, $hasRiver, $hasOldHouse, $landShape);
    $visualLayout = buildVisual2DLayout($best['name'], $type, $hasRoad, $hasRiver, $hasOldHouse);
    $businessPlanExport = buildBusinessPlanExport($best['name'], $district, $costs, $income, $roi);
    $budgetSimulator = buildBudgetSimulator($best['name'], $budget);
    $competitorComparison = buildCompetitorComparison($competitorsRaw, $best['name']);
    $customerPersonas = buildCustomerPersonas($best['name'], $target);
    $branding = buildBrandingGenerator($best['name'], $district);
    $monthlyActionPlan = buildMonthlyActionPlan($best['name']);
    $bookingModule = buildBookingModule($best['name']);
    $investorPitch = buildInvestorPitch($best['name'], $district, $budget, $income, $roi);
    $beforeAfter = buildBeforeAfterPlan($best['name'], $type);
    $advancedAnalytics = buildProjectAnalyticsSignals($type, $best['name'], $budget, $scores, $riskHeatmap);
    $scenarioComparison = buildScenarioComparison($best['name'], $budget, $income);

    return [
        'generated_at' => date('Y-m-d H:i:s'),
        'summary' => "Based on land type, budget, access, existing assets and risk signals, the best recommended model is {$best['name']} for {$district}.",
        'best_idea' => $best,
        'alternative_ideas' => array_slice($ideas, 1, 4),
        'business_comparison' => $comparison,
        'budget_tier' => $tier,
        'potential_scores' => $scores,
        'layout' => $layout,
        'layout_diagram' => $layoutDiagram,
        'costs' => $costs,
        'income' => $income,
        'roi' => $roi,
        'break_even' => $breakEven,
        'risks' => $risks,
        'risk_heatmap' => $riskHeatmap,
        'swot' => $swot,
        'phases' => $phases,
        'packages' => $packages,
        'facilities_checklist' => $facilities,
        'legal_checklist' => $legal,
        'marketing' => $marketing,
        'real_ai_api_suggestion' => $apiMode,
        'map_location_analysis' => $mapAnalysis,
        'image_ai_analysis' => $imageAnalysis,
        'visual_2d_layout' => $visualLayout,
        'business_plan_export' => $businessPlanExport,
        'budget_simulator' => $budgetSimulator,
        'competitor_comparison' => $competitorComparison,
        'customer_personas' => $customerPersonas,
        'branding_generator' => $branding,
        'monthly_action_plan' => $monthlyActionPlan,
        'booking_module' => $bookingModule,
        'investor_pitch' => $investorPitch,
        'before_after_plan' => $beforeAfter,
        'scenario_comparison' => $scenarioComparison,
        'advanced_analytics_signals' => $advancedAnalytics,
        'final_advice' => finalAdvice($tier, $best['name'], $budget, $size),
    ];
}

function buildIdeaScores(string $type, float $size, bool $hasRoad, bool $hasRiver, bool $hasOldHouse, string $notes): array
{
    $ideas = [];
    $addIdea = function($name, $score, $reason) use (&$ideas) {
        if (!isset($ideas[$name])) $ideas[$name] = ['name' => $name, 'score' => 0, 'reasons' => []];
        $ideas[$name]['score'] += $score;
        $ideas[$name]['reasons'][] = $reason;
    };

    switch ($type) {
        case 'river_side':
            $addIdea('River Cabana & Day Outing Resort', 42, 'River-side lands have strong relaxation, nature tourism and weekend stay value.');
            $addIdea('Riverside Cafe + Photo Booth Area', 28, 'A cafe and photo location can start faster with lower initial cost.');
            $addIdea('Glamping + BBQ Night Experience', 22, 'River ambience supports premium weekend and night packages.');
            break;
        case 'road_side':
            $addIdea('Smart Car Wash + Mini Cafe', 35, 'Road visibility supports vehicle traffic and repeat customers.');
            $addIdea('Tyre / Hardware / Electrical Mini Store', 30, 'Road-side access is suitable for retail and service income.');
            $addIdea('Container Cafe + Parking Business', 22, 'Modular commercial setup can start with lower construction cost.');
            break;
        case 'town':
            $addIdea('Student / Worker Rental Rooms', 38, 'Urban/town lands usually have demand for rental rooms and short stays.');
            $addIdea('Mini Food Court + Cloud Kitchen', 30, 'Population density supports food and delivery demand.');
            $addIdea('Paid Parking + Small Retail Units', 24, 'Town parking shortage can create daily cash flow.');
            break;
        case 'agricultural':
            $addIdea('Plant Nursery + Organic Fertilizer Store', 36, 'Agricultural setting is suitable for nursery, compost and farming supplies.');
            $addIdea('Farm Stay + Experience Garden', 26, 'Village/farm experience can attract families and school groups.');
            $addIdea('Hydroponic / Protected House Farming', 22, 'Controlled farming can improve land productivity.');
            break;
        case 'beach':
            $addIdea('Beach Cabana + Juice Bar', 42, 'Beach-side lands are strong for tourism, day visits and stays.');
            $addIdea('Surf / Beach Activity Rental Hub', 26, 'Activities can generate extra revenue from visitors.');
            $addIdea('Seafood BBQ Night Spot', 24, 'Beach night dining can attract weekend groups.');
            break;
        case 'mountain':
            $addIdea('View Point Cabana + Tea Cafe', 40, 'Mountain view is valuable for stays, photos and cafe experiences.');
            $addIdea('Adventure Camping Site', 26, 'Natural terrain suits camping and hiking packages.');
            $addIdea('Wellness Retreat / Yoga Stay', 22, 'Quiet view locations suit wellness and retreat businesses.');
            break;
        case 'village':
            $addIdea('Village Stay + Nature Experience', 32, 'Village lands can be marketed as peaceful local experience stays.');
            $addIdea('Plant Nursery + Organic Fertilizer Store', 25, 'Village setting supports agriculture related business.');
            $addIdea('Event Garden + Photo Location', 22, 'Open lands can be converted into low-cost event/photo spaces.');
            break;
        default:
            $addIdea('Multi-purpose Small Business Hub', 25, 'General lands should be developed in phases according to demand.');
            $addIdea('Storage + Retail Units', 18, 'Simple rental units reduce operational complexity.');
            break;
    }

    if ($hasRiver) {
        $addIdea('River Cabana & Day Outing Resort', 18, 'River access increases leisure, bathing and photography value.');
        $addIdea('Riverside Cafe + Photo Booth Area', 15, 'River view can attract visitors even with a small setup.');
    }
    if ($hasRoad) {
        $addIdea('Smart Car Wash + Mini Cafe', 12, 'Road access improves customer entry and service visibility.');
        $addIdea('Container Cafe + Parking Business', 10, 'Easy access supports parking and cafe operations.');
    }
    if ($hasOldHouse) {
        $addIdea('Renovated Old House Boutique Stay', 18, 'Existing building can reduce construction cost and add character.');
        $addIdea('Cafe + Event Space Using Old House', 12, 'Old house can become reception, dining or indoor seating.');
    }
    if ($size >= 40) {
        $addIdea('River Cabana & Day Outing Resort', 10, 'Larger land supports privacy, parking and multiple zones.');
        $addIdea('Farm Stay + Experience Garden', 8, 'Larger land can include gardens and activities.');
    } elseif ($size <= 15) {
        $addIdea('Container Cafe + Parking Business', 8, 'Small lands need compact and fast-return business models.');
        $addIdea('Riverside Cafe + Photo Booth Area', 6, 'Small leisure setup can work with limited space.');
    }
    if (str_contains($notes, 'student') || str_contains($notes, 'campus') || str_contains($notes, 'university')) $addIdea('Student / Worker Rental Rooms', 14, 'Nearby student/customer demand appears in notes.');
    if (str_contains($notes, 'organic') || str_contains($notes, 'fertilizer') || str_contains($notes, 'plant')) $addIdea('Plant Nursery + Organic Fertilizer Store', 14, 'Agriculture-related demand appears in notes.');
    if (str_contains($notes, 'couple') || str_contains($notes, 'tourist') || str_contains($notes, 'photo')) $addIdea('River Cabana & Day Outing Resort', 8, 'Target demand supports experience based business.');

    return $ideas;
}

function budgetTier(float $budget): array
{
    if ($budget <= 500000) return ['name' => 'Micro Start', 'description' => 'Start with a small, testable version. Avoid heavy construction.'];
    if ($budget <= 1500000) return ['name' => 'Starter', 'description' => 'Build the first income unit and add supporting facilities gradually.'];
    if ($budget <= 3500000) return ['name' => 'Standard', 'description' => 'Enough for a professional first phase with branding and core facilities.'];
    return ['name' => 'Premium', 'description' => 'Can build a strong commercial version with multiple revenue streams.'];
}

function buildCostPlan(string $idea, float $budget, float $size, bool $oldHouse, bool $river): array
{
    $baseMultiplier = $size > 40 ? 1.12 : ($size < 15 ? 0.9 : 1.0);
    if (str_contains($idea, 'Cabana') || str_contains($idea, 'Glamping') || str_contains($idea, 'Stay') || str_contains($idea, 'Resort')) {
        $items = [
            ['item' => 'Site clearing, land preparation and access path', 'cost' => 180000 * $baseMultiplier],
            ['item' => $oldHouse ? 'Old house renovation for reception/dining' : 'Small reception and dining shelter', 'cost' => $oldHouse ? 450000 : 650000],
            ['item' => '2 starter cabanas / glamping units', 'cost' => 1200000 * $baseMultiplier],
            ['item' => 'Washrooms and changing rooms', 'cost' => 450000],
            ['item' => $river ? 'River safety steps, lighting and sign boards' : 'Garden seating and pathway lights', 'cost' => $river ? 350000 : 250000],
            ['item' => 'Branding, booking setup and photo/video marketing', 'cost' => 150000],
            ['item' => 'Contingency reserve', 'cost' => 250000],
        ];
    } elseif (str_contains($idea, 'Cafe') || str_contains($idea, 'Food')) {
        $items = [
            ['item' => 'Container/wooden cafe structure', 'cost' => 650000 * $baseMultiplier],
            ['item' => 'Kitchen setup and equipment', 'cost' => 450000],
            ['item' => 'Outdoor seating and lighting', 'cost' => 250000 * $baseMultiplier],
            ['item' => 'Photo booth / sign board wall', 'cost' => 120000],
            ['item' => 'Initial stock and packaging', 'cost' => 150000],
            ['item' => 'Marketing launch campaign', 'cost' => 80000],
            ['item' => 'Contingency reserve', 'cost' => 150000],
        ];
    } elseif (str_contains($idea, 'Car Wash')) {
        $items = [
            ['item' => 'Concrete washing bay and drainage', 'cost' => 550000 * $baseMultiplier],
            ['item' => 'Pressure washer and equipment', 'cost' => 250000],
            ['item' => 'Water tank and plumbing', 'cost' => 220000],
            ['item' => 'Small cafe / waiting area', 'cost' => 300000],
            ['item' => 'Sign boards and launch marketing', 'cost' => 90000],
            ['item' => 'Contingency reserve', 'cost' => 150000],
        ];
    } elseif (str_contains($idea, 'Nursery') || str_contains($idea, 'Fertilizer')) {
        $items = [
            ['item' => 'Shade net / nursery structure', 'cost' => 450000 * $baseMultiplier],
            ['item' => 'Plant racks, irrigation and water storage', 'cost' => 250000],
            ['item' => 'Initial plant and fertilizer stock', 'cost' => 350000],
            ['item' => 'Small sales counter and storage', 'cost' => 250000],
            ['item' => 'Branding and social media launch', 'cost' => 70000],
            ['item' => 'Contingency reserve', 'cost' => 120000],
        ];
    } else {
        $items = [
            ['item' => 'Basic land preparation', 'cost' => 200000 * $baseMultiplier],
            ['item' => 'Starter business structure', 'cost' => 650000],
            ['item' => 'Utilities and lighting', 'cost' => 250000],
            ['item' => 'Furniture/equipment', 'cost' => 300000],
            ['item' => 'Branding and launch marketing', 'cost' => 100000],
            ['item' => 'Contingency reserve', 'cost' => 150000],
        ];
    }
    $total = array_sum(array_column($items, 'cost'));
    $gap = $budget - $total;
    return [
        'items' => array_map(fn($row) => ['item' => $row['item'], 'cost' => round($row['cost'])], $items),
        'estimated_total' => round($total),
        'user_budget' => round($budget),
        'budget_gap' => round($gap),
        'budget_status' => $gap >= 0 ? 'Within budget' : 'Budget shortage',
        'note' => $gap >= 0 ? 'Your budget can cover the suggested first phase.' : 'Reduce scope or develop in phases to fit the budget.',
    ];
}

function estimateIncome(string $idea, float $budget, float $size, string $district): array
{
    $low = 80000; $high = 180000; $streams = [];
    if (str_contains($idea, 'Cabana') || str_contains($idea, 'Glamping') || str_contains($idea, 'Stay') || str_contains($idea, 'Resort')) {
        $unitCount = $budget > 3000000 ? 3 : 2;
        $avgPrice = $budget > 3000000 ? 9000 : 6500;
        $monthlyBookedNights = $unitCount * 12;
        $low = $monthlyBookedNights * $avgPrice + 45000;
        $high = ($unitCount * 18 * ($avgPrice + 1500)) + 120000;
        $streams = ['Room/cabana bookings', 'Day outing packages', 'Food and drinks', 'Photo location charges', 'BBQ/night event packages'];
    } elseif (str_contains($idea, 'Cafe') || str_contains($idea, 'Food')) {
        $dailyCustomers = $budget > 1500000 ? 45 : 25;
        $avgBill = $budget > 1500000 ? 950 : 650;
        $low = $dailyCustomers * $avgBill * 22;
        $high = ($dailyCustomers + 25) * ($avgBill + 200) * 26;
        $streams = ['Food sales', 'Drinks', 'Birthday/photo bookings', 'Delivery/pre-orders'];
    } elseif (str_contains($idea, 'Car Wash')) {
        $low = 18 * 850 * 24; $high = 35 * 1100 * 26;
        $streams = ['Car wash', 'Bike wash', 'Detailing', 'Mini cafe waiting area'];
    } elseif (str_contains($idea, 'Nursery') || str_contains($idea, 'Fertilizer')) {
        $low = 140000; $high = 420000;
        $streams = ['Plant sales', 'Organic fertilizer packs', 'Delivery orders', 'Landscaping packages'];
    } else {
        $low = 100000; $high = 300000;
        $streams = ['Main business sales', 'Rental/extra services', 'Online orders'];
    }
    $avgRevenue = ($low + $high) / 2;
    $monthlyExpenses = round($avgRevenue * 0.60);
    $avgMonthlyProfit = max(1, $avgRevenue - $monthlyExpenses);
    $paybackMonths = max(1, ceil(max(1, $budget) / $avgMonthlyProfit));
    return [
        'monthly_revenue_low' => round($low),
        'monthly_revenue_high' => round($high),
        'average_monthly_revenue' => round($avgRevenue),
        'estimated_monthly_expenses' => $monthlyExpenses,
        'estimated_net_profit' => round($avgMonthlyProfit),
        'expected_profit_margin' => '25% - 40% after operations stabilize',
        'estimated_payback_months' => $paybackMonths,
        'income_streams' => $streams,
        'assumption' => 'This is an MVP feasibility estimate. Real income depends on location, quality, pricing, marketing, season and management.',
    ];
}

function buildPotentialScores(string $type, float $size, float $budget, bool $road, bool $river, bool $oldHouse, array $risks, array $income): array
{
    $tourismTypes = ['river_side','beach','mountain','village'];
    $tourism = in_array($type, $tourismTypes) ? 72 : 45;
    if ($river) $tourism += 16;
    if ($oldHouse) $tourism += 6;
    $access = $road ? 82 : 46;
    $construction = 72 + ($budget > 2500000 ? 10 : 0) + ($oldHouse ? 4 : 0) - ($river ? 6 : 0);
    $roi = $income['estimated_payback_months'] <= 18 ? 84 : ($income['estimated_payback_months'] <= 30 ? 70 : 56);
    $overall = round(($tourism * .25) + ($access * .20) + ($construction * .20) + ($roi * .25) + (min(100, $size + 35) * .10));
    return [
        ['label' => 'Overall Business Potential', 'score' => clampScore($overall), 'level' => scoreLevel($overall)],
        ['label' => 'Tourism / Customer Attraction', 'score' => clampScore($tourism), 'level' => scoreLevel($tourism)],
        ['label' => 'Access Quality', 'score' => clampScore($access), 'level' => scoreLevel($access)],
        ['label' => 'Construction Feasibility', 'score' => clampScore($construction), 'level' => scoreLevel($construction)],
        ['label' => 'Return on Investment', 'score' => clampScore($roi), 'level' => scoreLevel($roi)],
    ];
}
function clampScore($v){ return max(0, min(100, round($v))); }
function scoreLevel($score){ return $score >= 80 ? 'High' : ($score >= 60 ? 'Medium' : 'Low'); }

function buildBusinessComparison(array $ideas, string $bestName): array
{
    $rows = [];
    foreach (array_slice($ideas, 0, 4) as $idea) {
        $name = $idea['name'];
        $cost = (str_contains($name,'Cabana') || str_contains($name,'Stay') || str_contains($name,'Resort')) ? 'High' : ((str_contains($name,'Cafe') || str_contains($name,'Car Wash')) ? 'Medium' : 'Low / Medium');
        $income = (str_contains($name,'Cabana') || str_contains($name,'Resort') || str_contains($name,'Rooms')) ? 'High' : 'Medium';
        $risk = (str_contains($name,'River') || str_contains($name,'Beach') || str_contains($name,'Cabana')) ? 'Medium' : 'Low / Medium';
        $rows[] = ['idea' => $name, 'cost_level' => $cost, 'income_potential' => $income, 'risk_level' => $risk, 'recommendation' => $name === $bestName ? 'Best Option' : 'Alternative'];
    }
    return $rows;
}

function buildRoiPlan(float $budget, array $costs, array $income): array
{
    $investment = max($budget, (float)($costs['estimated_total'] ?? $budget));
    $monthlyRevenue = (float)($income['average_monthly_revenue'] ?? 0);
    $expenses = (float)($income['estimated_monthly_expenses'] ?? ($monthlyRevenue * 0.60));
    $net = max(1, $monthlyRevenue - $expenses);
    return [
        'estimated_investment' => round($investment),
        'average_monthly_income' => round($monthlyRevenue),
        'estimated_monthly_expenses' => round($expenses),
        'estimated_net_profit' => round($net),
        'payback_period_months' => max(1, ceil($investment / $net)),
        'note' => 'ROI is calculated using average revenue and estimated operating expenses. Use real quotations before investing.',
    ];
}

function buildRiskHeatmap(string $type, bool $road, bool $river, bool $oldHouse, float $budget, float $size): array
{
    return [
        ['risk' => 'Flood / Weather Risk', 'level' => ($river || $type === 'river_side' || $type === 'beach') ? 'High' : 'Low'],
        ['risk' => 'Construction Cost Risk', 'level' => $budget < 1500000 ? 'High' : ($budget < 3500000 ? 'Medium' : 'Low')],
        ['risk' => 'Customer Safety Risk', 'level' => $river ? 'Medium' : 'Low'],
        ['risk' => 'Parking / Access Risk', 'level' => !$road ? 'High' : ($size < 15 ? 'Medium' : 'Low')],
        ['risk' => 'Marketing Risk', 'level' => 'Medium'],
        ['risk' => 'Maintenance Risk', 'level' => $oldHouse ? 'Medium' : 'Low'],
    ];
}

function buildSwot(string $idea, string $type, bool $road, bool $river, bool $oldHouse, float $budget): array
{
    return [
        'strengths' => array_values(array_filter([
            $river ? 'Natural water view and leisure value' : null,
            $road ? 'Vehicle access and easier customer arrival' : null,
            $oldHouse ? 'Existing building can reduce first phase cost' : null,
            'Can create multiple income streams using one land property',
        ])),
        'weaknesses' => array_values(array_filter([
            !$road ? 'Access improvement is required before launch' : null,
            $budget < 1500000 ? 'Budget may be low for a full commercial setup' : null,
            $river ? 'Safety management near water is compulsory' : null,
            'Requires strong service quality and maintenance',
        ])),
        'opportunities' => ['Weekend tourism packages', 'Social media photo location marketing', 'Online booking and WhatsApp reservations', 'Partnerships with photographers, food suppliers and travel pages'],
        'threats' => ['Weather and seasonal demand changes', 'Competitors copying packages', 'Construction cost changes', 'Poor reviews if cleanliness or safety is weak'],
    ];
}

function buildPackages(string $idea, string $district): array
{
    if (str_contains($idea, 'Cabana') || str_contains($idea, 'Glamping') || str_contains($idea, 'Stay') || str_contains($idea, 'Resort')) {
        return [
            ['name' => 'Couple Day Out Package', 'price' => 'Rs. 4,500 - 6,500', 'includes' => 'Private seating, welcome drink, photo area access'],
            ['name' => 'Night Stay Couple Package', 'price' => 'Rs. 8,500 - 12,000', 'includes' => 'Cabana stay, breakfast option, river view'],
            ['name' => 'Family River Day Package', 'price' => 'Rs. 8,000 - 15,000', 'includes' => 'Day outing, dining area, river/photo access'],
            ['name' => 'Birthday / Photoshoot Package', 'price' => 'Rs. 10,000 - 20,000', 'includes' => 'Decor area, photo booth, outdoor seating'],
        ];
    }
    if (str_contains($idea, 'Cafe') || str_contains($idea, 'Food')) {
        return [
            ['name' => 'Opening Combo Offer', 'price' => 'Rs. 750 - 1,500', 'includes' => 'Drink + snack / meal combo'],
            ['name' => 'Birthday Table Booking', 'price' => 'Rs. 5,000 - 15,000', 'includes' => 'Decor table, food package, photo point'],
            ['name' => 'Weekend Family Set', 'price' => 'Rs. 4,000 - 9,000', 'includes' => 'Food platter and outdoor seating'],
        ];
    }
    return [
        ['name' => 'Starter Customer Package', 'price' => 'Custom', 'includes' => 'Basic service package for first customers'],
        ['name' => 'Monthly Membership / Repeat Offer', 'price' => 'Custom', 'includes' => 'Discount for repeat customers'],
        ['name' => 'Group Booking Package', 'price' => 'Custom', 'includes' => 'Special rate for groups and events'],
    ];
}

function buildFacilitiesChecklist(string $idea, bool $river, bool $oldHouse): array
{
    $items = ['Parking area', 'Reception / booking point', 'Clean washrooms', 'Lighting', 'CCTV or security lighting', 'First aid box', 'Waste bins', 'Google Maps location', 'WhatsApp booking number', 'Customer review system'];
    if ($river) $items = array_merge($items, ['River safety barrier', 'Warning boards', 'Changing rooms', 'Non-slip steps', 'Emergency rope / safety equipment']);
    if (str_contains($idea,'Cabana') || str_contains($idea,'Stay') || str_contains($idea,'Resort')) $items = array_merge($items, ['Private cabana units', 'Bed linen checklist', 'Outdoor seating', 'Food/drinks counter']);
    if ($oldHouse) $items[] = 'Old house safety inspection report';
    return array_values(array_unique($items));
}

function buildLegalChecklist(string $idea): array
{
    $items = ['Business registration', 'Local council / Pradeshiya Sabha permission if required', 'Land ownership or lease documents', 'Basic tax/invoice records', 'Public liability and safety notice planning'];
    if (str_contains($idea,'Cafe') || str_contains($idea,'Food') || str_contains($idea,'BBQ')) $items[] = 'Food handling / health related approval if food is sold';
    if (str_contains($idea,'Cabana') || str_contains($idea,'Stay') || str_contains($idea,'Resort')) $items[] = 'Accommodation/tourism related registration requirements if operating as a stay';
    $items[] = 'Environmental or river/beach related approval check if the land is near protected water area';
    return $items;
}

function buildBreakEven(string $idea, array $income, float $budget): array
{
    $expenses = (float)($income['estimated_monthly_expenses'] ?? 100000);
    if (str_contains($idea,'Cabana') || str_contains($idea,'Stay') || str_contains($idea,'Resort')) {
        $nightPrice = $budget > 3000000 ? 9500 : 6500;
        $dayPrice = 4500;
        return [
            'summary' => 'To cover monthly expenses, focus on a balanced mix of night stays and day-outing bookings.',
            'targets' => [
                ceil($expenses / $nightPrice) . ' night bookings per month',
                ceil(($expenses * 0.55) / $dayPrice) . ' day-outing packages + ' . ceil(($expenses * 0.45) / $nightPrice) . ' night bookings',
            ],
        ];
    }
    return ['summary' => 'Break-even depends on daily customer count and average bill value.', 'targets' => [ceil($expenses / 1000) . ' average Rs. 1,000 customer orders per month', ceil($expenses / 26) . ' daily net revenue target for 26 business days']];
}

function buildLayoutDiagram(string $idea, bool $road, bool $river, bool $oldHouse): array
{
    $diagram = [];
    $diagram[] = $road ? 'Entrance + Sign Board + Parking' : 'Access Path + Direction Boards';
    if ($oldHouse) $diagram[] = 'Old House: Reception / Cafe / Office';
    else $diagram[] = 'Reception + Small Service Counter';
    if (str_contains($idea,'Cabana') || str_contains($idea,'Stay') || str_contains($idea,'Resort')) {
        $diagram[] = 'Garden Path + Photo Booth';
        $diagram[] = 'Private Cabana / Glamping Zone';
        $diagram[] = 'Dining + Live Kitchen Area';
    } else {
        $diagram[] = 'Main Business Operation Zone';
        $diagram[] = 'Customer Seating / Waiting Area';
    }
    $diagram[] = 'Washrooms + Staff / Storage Area';
    if ($river) $diagram[] = 'River View Deck + Safety Barrier + Changing Area';
    $diagram[] = 'Future Expansion Area';
    return $diagram;
}

function buildLayout(string $idea, string $type, float $size, bool $road, bool $river, bool $oldHouse): array
{
    $zones = [];
    if ($road) { $zones[] = ['zone' => 'Entrance & sign board', 'purpose' => 'Clear visibility, customer arrival and brand identity.']; $zones[] = ['zone' => 'Parking area', 'purpose' => 'Keep vehicles away from main activity area.']; }
    else $zones[] = ['zone' => 'Defined access path', 'purpose' => 'Improve customer flow and emergency access.'];
    if ($oldHouse) $zones[] = ['zone' => 'Old house conversion', 'purpose' => 'Use as reception, dining, office, storage or cafe.'];
    if (str_contains($idea, 'Cabana') || str_contains($idea, 'Glamping') || str_contains($idea, 'Stay') || str_contains($idea, 'Resort')) {
        $zones[] = ['zone' => 'Private accommodation zone', 'purpose' => 'Place cabanas with privacy, view and safe walking paths.'];
        $zones[] = ['zone' => 'Dining / live kitchen zone', 'purpose' => 'Central shared area for food and events.'];
        $zones[] = ['zone' => 'Washroom and changing room zone', 'purpose' => 'Easy access for guests and day visitors.'];
        $zones[] = ['zone' => 'Garden photo booth zone', 'purpose' => 'Create shareable social media moments.'];
    } elseif (str_contains($idea, 'Cafe') || str_contains($idea, 'Food')) {
        $zones[] = ['zone' => 'Kitchen and counter zone', 'purpose' => 'Fast food preparation and billing.'];
        $zones[] = ['zone' => 'Outdoor seating zone', 'purpose' => 'Attractive seating with lighting and photo points.'];
        $zones[] = ['zone' => 'Service/storage zone', 'purpose' => 'Keep supplies, gas and stock safely.'];
    } elseif (str_contains($idea, 'Car Wash')) {
        $zones[] = ['zone' => 'Wash bay zone', 'purpose' => 'Vehicle washing with correct drainage.'];
        $zones[] = ['zone' => 'Dry/detailing zone', 'purpose' => 'Add premium cleaning packages.'];
        $zones[] = ['zone' => 'Waiting/cafe zone', 'purpose' => 'Earn extra while customers wait.'];
    } elseif (str_contains($idea, 'Nursery')) {
        $zones[] = ['zone' => 'Plant display zone', 'purpose' => 'Easy browsing and product visibility.'];
        $zones[] = ['zone' => 'Shade net growing zone', 'purpose' => 'Protect plants and manage growth.'];
        $zones[] = ['zone' => 'Fertilizer/storage zone', 'purpose' => 'Keep stock organized and dry.'];
    } else {
        $zones[] = ['zone' => 'Main commercial zone', 'purpose' => 'Core business activity area.'];
        $zones[] = ['zone' => 'Support/storage zone', 'purpose' => 'Operations and maintenance.'];
    }
    if ($river) $zones[] = ['zone' => 'River safety and viewing zone', 'purpose' => 'Steps, barriers, lighting and warning signs for safe use.'];
    $zones[] = ['zone' => 'Future expansion zone', 'purpose' => $size > 25 ? 'Keep space for second phase expansion.' : 'Use compact layout and avoid blocking expansion paths.'];
    return $zones;
}

function buildRisks(string $type, bool $road, bool $river, bool $oldHouse, float $budget, float $size): array
{
    $risks = [];
    if ($river || $type === 'river_side') {
        $risks[] = ['risk' => 'Flood and safety risk', 'solution' => 'Check flood levels, keep buildings elevated, add railings, signs and lighting.'];
        $risks[] = ['risk' => 'Guest accident risk near water', 'solution' => 'Create safe river access, no-slip steps and emergency equipment.'];
    }
    if (!$road) $risks[] = ['risk' => 'Poor access can reduce customer visits', 'solution' => 'Improve entrance path and add clear location pins/sign boards.'];
    if ($oldHouse) $risks[] = ['risk' => 'Old building repair cost can increase', 'solution' => 'Inspect roof, wiring and walls before renovation.'];
    if ($budget < 700000) $risks[] = ['risk' => 'Budget too low for full commercial setup', 'solution' => 'Start with photo location, cafe counter or day-outing pilot phase.'];
    if ($size < 10) $risks[] = ['risk' => 'Limited space for parking and privacy', 'solution' => 'Use compact structures and pre-booking only model.'];
    $risks[] = ['risk' => 'Low bookings during off-season', 'solution' => 'Add local day packages, events, TikTok/Facebook marketing and weekday discounts.'];
    $risks[] = ['risk' => 'Operations quality risk', 'solution' => 'Use booking rules, cleanliness checklist and customer review follow-up.'];
    return $risks;
}

function buildPhases(array $tier, string $idea): array
{
    if ($tier['name'] === 'Micro Start') return [
        ['phase' => 'Phase 1', 'work' => 'Clean land, create entrance, lights, seating, photo booth and test weekend demand.'],
        ['phase' => 'Phase 2', 'work' => 'Add small cafe/drinks counter and basic washroom.'],
        ['phase' => 'Phase 3', 'work' => 'Reinvest income into cabana/room or premium service.'],
    ];
    if ($tier['name'] === 'Starter') return [
        ['phase' => 'Phase 1', 'work' => 'Build one income area first: cafe, one cabana, nursery or service bay.'],
        ['phase' => 'Phase 2', 'work' => 'Add washroom, parking, branding and online booking.'],
        ['phase' => 'Phase 3', 'work' => 'Expand with second income stream after demand is proven.'],
    ];
    return [
        ['phase' => 'Phase 1', 'work' => 'Build professional base setup with branding, access, safety and core facilities.'],
        ['phase' => 'Phase 2', 'work' => 'Launch with social media campaign, offers and customer feedback system.'],
        ['phase' => 'Phase 3', 'work' => 'Add premium features, partnerships and advanced booking packages.'],
    ];
}

function buildMarketing(string $idea, string $district, string $target): array
{
    return [
        "Create a Facebook and TikTok page for the {$idea} in {$district}.",
        'Post before/after land development videos to build trust and curiosity.',
        "Target {$target} with weekend offers and opening discounts.",
        'Use Google Maps location, WhatsApp booking link and customer reviews.',
        'Partner with local photographers, food suppliers, travel pages or schools/companies depending on the model.',
        'Create short video content: road access, view, rooms, food, safety and customer reviews.',
    ];
}

function finalAdvice(array $tier, string $idea, float $budget, float $size): string
{
    if ($tier['name'] === 'Micro Start') return "Do not try to build the full {$idea} immediately. Start with the smallest income-generating version and prove demand first.";
    if ($tier['name'] === 'Starter') return "Build one strong first phase of {$idea}, collect customer feedback, then expand using real income data.";
    return "You have enough budget for a professional launch. Focus on branding, safety, customer experience and online booking from day one.";
}


function extractNoteValue(string $notes, string $label): string
{
    if (preg_match('/' . preg_quote($label, '/') . '\s*:\s*([^\n]+)/i', $notes, $m)) return trim($m[1]);
    return '';
}

function buildRealAiApiSuggestion(string $notes): array
{
    return [
        'mode' => 'Offline rule-based AI enabled',
        'upgrade_ready' => true,
        'recommended_next' => 'Connect OpenAI or Gemini API in ai_engine.php to generate more natural reports from the same project data.',
        'prompt_template' => 'Analyze this land data and generate feasibility, cost, income, risks, layout, customer personas, branding, investor pitch and 30-day action plan.',
        'safety_note' => 'Keep the offline engine as a fallback when the API key is missing or internet is unavailable.'
    ];
}

function buildMapLocationAnalysis(string $district, string $mapLink, string $type, bool $road, bool $river): array
{
    $signals = [];
    $signals[] = $mapLink ? 'Google Map link provided, so the report can be attached to a real location.' : 'No map link provided. Add Google Map link to improve location confidence.';
    $signals[] = $road ? 'Road access signal is positive for customer entry, signage and delivery.' : 'Road access must be verified before investment.';
    if ($river) $signals[] = 'Water feature can improve visitor attraction but needs safety and seasonal checks.';
    $signals[] = 'Use the map to manually check competitors within 3km, main road distance, nearest town and customer demand points.';
    return ['district' => $district, 'map_link' => $mapLink, 'location_confidence' => $mapLink ? 'Medium/High' : 'Medium', 'signals' => $signals];
}

function buildImageAiAnalysis(string $type, bool $road, bool $river, bool $oldHouse, string $shape): array
{
    $detected = [];
    $detected[] = ucwords(str_replace('_',' ', $type));
    if ($road) $detected[] = 'Possible road/entrance access';
    if ($river) $detected[] = 'Water/river feature';
    if ($oldHouse) $detected[] = 'Existing building asset';
    if ($shape) $detected[] = 'Land shape noted: ' . $shape;
    return [
        'status' => 'Simulated image intelligence for MVP',
        'detected_features' => $detected,
        'development_meaning' => 'The uploaded image should be used to confirm access, slope, trees, open space, existing structures and customer-facing view points.',
        'future_upgrade' => 'Connect a vision AI model to detect road, water, building, trees, slope and usable area automatically.'
    ];
}

function buildVisual2DLayout(string $idea, string $type, bool $road, bool $river, bool $oldHouse): array
{
    if (str_contains($idea, 'Cabana') || str_contains($idea, 'Stay') || str_contains($idea, 'Resort')) {
        return [
            ['zone'=>'Entrance / Security', 'position'=>'top-left', 'purpose'=>'Control entry and direct customers.'],
            ['zone'=>'Parking', 'position'=>'top-right', 'purpose'=>'Keep vehicles away from quiet guest areas.'],
            ['zone'=>$oldHouse ? 'Old House Reception' : 'Reception + Cafe', 'position'=>'middle-left', 'purpose'=>'Check-in, dining and staff operations.'],
            ['zone'=>'Garden Path / Photo Area', 'position'=>'middle-right', 'purpose'=>'Improve walking flow and social media value.'],
            ['zone'=>'Cabana Units', 'position'=>'bottom-left', 'purpose'=>'Private stay units facing best view.'],
            ['zone'=>$river ? 'River Deck + Safety Barrier' : 'View Deck / Activity Lawn', 'position'=>'bottom-right', 'purpose'=>'Main attraction zone with safety control.'],
        ];
    }
    if (str_contains($idea, 'Car Wash')) {
        return [
            ['zone'=>'Main Road Sign Board', 'position'=>'top-left', 'purpose'=>'Attract passing vehicles.'],
            ['zone'=>'Entry / Queue Lane', 'position'=>'top-right', 'purpose'=>'Prevent traffic congestion.'],
            ['zone'=>'Washing Bay', 'position'=>'middle-left', 'purpose'=>'Main revenue operation.'],
            ['zone'=>'Cafe / Waiting Area', 'position'=>'middle-right', 'purpose'=>'Extra income while customers wait.'],
            ['zone'=>'Water Tank + Storage', 'position'=>'bottom-left', 'purpose'=>'Support service operations.'],
            ['zone'=>'Exit / Future Expansion', 'position'=>'bottom-right', 'purpose'=>'Smooth customer flow and future growth.'],
        ];
    }
    return [
        ['zone'=>'Entrance', 'position'=>'top-left', 'purpose'=>'Customer arrival.'],
        ['zone'=>'Parking', 'position'=>'top-right', 'purpose'=>'Vehicle management.'],
        ['zone'=>'Main Business Unit', 'position'=>'middle-left', 'purpose'=>'Primary income area.'],
        ['zone'=>'Customer Area', 'position'=>'middle-right', 'purpose'=>'Waiting/seating.'],
        ['zone'=>'Storage / Staff', 'position'=>'bottom-left', 'purpose'=>'Operations.'],
        ['zone'=>'Expansion Space', 'position'=>'bottom-right', 'purpose'=>'Future growth.'],
    ];
}

function buildBusinessPlanExport(string $idea, string $district, array $costs, array $income, array $roi): array
{
    return [
        'title' => $idea . ' Business Plan - ' . $district,
        'sections' => [
            'Business Overview' => 'Develop the selected land into a focused income-generating model: ' . $idea . '.',
            'Market Opportunity' => 'Target local demand, weekend visitors and nearby customer segments using location advantages.',
            'Investment Plan' => 'Estimated first phase investment is around Rs. ' . number_format($costs['estimated_total'] ?? 0) . '.',
            'Revenue Model' => 'Expected average monthly revenue is around Rs. ' . number_format($income['average_monthly_revenue'] ?? 0) . '.',
            'Risk Plan' => 'Develop in phases, validate customer demand and keep contingency for cost changes.',
            'Conclusion' => 'Start with the smallest profitable version and expand based on bookings/sales.'
        ]
    ];
}

function buildBudgetSimulator(string $idea, float $budget): array
{
    return [
        ['budget'=>'Rs. 500,000', 'plan'=>'Micro pilot: clean land, sign board, social media page, basic seating or inquiry setup.', 'fit'=>$budget <= 700000 ? 'Current level' : 'Low budget option'],
        ['budget'=>'Rs. 1,500,000', 'plan'=>'Starter phase: one income unit, basic washroom/utility setup, launch marketing.', 'fit'=>$budget > 700000 && $budget <= 2000000 ? 'Current level' : 'Alternative'],
        ['budget'=>'Rs. 3,500,000', 'plan'=>'Standard phase: stronger construction, multiple packages, better branding and facilities.', 'fit'=>$budget > 2000000 && $budget <= 4500000 ? 'Current level' : 'Alternative'],
        ['budget'=>'Rs. 5,000,000+', 'plan'=>'Premium phase: full customer experience, multiple revenue streams and professional operations.', 'fit'=>$budget > 4500000 ? 'Current level' : 'Future target'],
    ];
}

function buildCompetitorComparison(string $competitorsRaw, string $idea): array
{
    $items = array_filter(array_map('trim', preg_split('/[,;]+/', $competitorsRaw ?: 'nearby small competitors, indirect online competitors')));
    $rows = [];
    foreach (array_slice($items,0,4) as $c) {
        $rows[] = ['competitor'=>$c, 'their_strength'=>'Existing awareness or location familiarity', 'your_opportunity'=>'Differentiate with better packages, online booking, clean branding and customer reviews'];
    }
    $rows[] = ['competitor'=>'Your proposed model: ' . $idea, 'their_strength'=>'New concept can be positioned professionally', 'your_opportunity'=>'Use launch offer, Google Map reviews and social media videos to build trust quickly'];
    return $rows;
}

function buildCustomerPersonas(string $idea, string $target): array
{
    $base = [
        ['name'=>'Weekend Couple Visitors', 'need'=>'Privacy, photos, safe location and easy booking', 'offer'=>'Couple package with advance booking and photo spots'],
        ['name'=>'Family Groups', 'need'=>'Clean washrooms, parking, food and safety', 'offer'=>'Family package with seating and child-safe zones'],
        ['name'=>'Office / Friend Groups', 'need'=>'Group activity, food and affordable packages', 'offer'=>'Day outing or combo package'],
    ];
    if (str_contains($idea, 'Car Wash')) $base = [
        ['name'=>'Daily Vehicle Owners', 'need'=>'Fast, clean and reliable service', 'offer'=>'Basic wash + loyalty card'],
        ['name'=>'Office Commuters', 'need'=>'Waiting area and quick service', 'offer'=>'Mini cafe waiting package'],
        ['name'=>'Bike Riders', 'need'=>'Affordable regular wash', 'offer'=>'Low-cost bike wash bundle'],
    ];
    if ($target) $base[] = ['name'=>'User Target Segment', 'need'=>$target, 'offer'=>'Custom package based on the selected target customer group'];
    return $base;
}

function buildBrandingGenerator(string $idea, string $district): array
{
    $clean = preg_replace('/[^A-Za-z ]/', '', $idea);
    $word = str_contains($idea,'River') ? 'River' : (str_contains($idea,'Cafe') ? 'Cafe' : (str_contains($idea,'Car Wash') ? 'Wash' : 'Land'));
    return [
        'name_ideas' => [$district . ' ' . $word . ' Hub', 'GreenEdge ' . $word, 'Vista ' . $word . ' Point', 'NatureNest ' . $word],
        'slogans' => ['Build your land into income.', 'A smart place for better experiences.', 'Simple start. Strong growth.', 'Plan better. Earn smarter.'],
        'brand_colors' => ['Green + White for nature/trust', 'Blue + White for water/clean service', 'Black + Gold for premium resort feel'],
        'logo_idea' => 'Use a simple icon combining land, road/water line and a small business building.'
    ];
}

function buildMonthlyActionPlan(string $idea): array
{
    return [
        ['week'=>'Week 1', 'tasks'=>['Measure land and confirm boundaries', 'Check road access, utilities and safety issues', 'Take clear photos/videos for planning']],
        ['week'=>'Week 2', 'tasks'=>['Collect quotations for core facilities', 'Choose phase 1 budget', 'Create Facebook/TikTok/Google Map presence']],
        ['week'=>'Week 3', 'tasks'=>['Start land cleaning and basic construction', 'Design packages and pricing', 'Prepare booking/inquiry workflow']],
        ['week'=>'Week 4', 'tasks'=>['Launch soft opening campaign', 'Collect customer feedback', 'Improve facilities before full launch']],
    ];
}

function buildBookingModule(string $idea): array
{
    return [
        'purpose' => 'Convert the feasibility report into real customer inquiries after business launch.',
        'fields' => ['Customer Name', 'Phone Number', 'Package / Service', 'Preferred Date', 'Advance Payment Status', 'Notes'],
        'statuses' => ['New Inquiry', 'Confirmed', 'Advance Paid', 'Completed', 'Cancelled'],
        'suggestion' => 'Add this mini booking module to manage inquiries for ' . $idea . '.'
    ];
}

function buildInvestorPitch(string $idea, string $district, float $budget, array $income, array $roi): string
{
    return 'This proposal recommends developing the land in ' . $district . ' as a ' . $idea . '. The model is designed to start in phases, reduce early risk, and generate multiple income streams. With an estimated investment of Rs. ' . number_format($budget) . ', the project can target an average monthly revenue of around Rs. ' . number_format($income['average_monthly_revenue'] ?? 0) . ' and a potential payback period of about ' . ($roi['payback_period_months'] ?? ($income['estimated_payback_months'] ?? '-')) . ' months, depending on location quality, pricing, marketing and management.';
}

function buildBeforeAfterPlan(string $idea, string $type): array
{
    return [
        'before' => ['Unorganized land space', 'No clear customer flow', 'Limited branding and safety facilities', 'No package or booking system'],
        'after' => ['Clear entrance and sign board', 'Defined business zones and customer path', 'Professional photo-friendly areas', 'Packages, online inquiries and review system'],
        'image_prompt' => 'Create an after-development concept render for a ' . ucwords(str_replace('_',' ', $type)) . ' converted into ' . $idea . ' with entrance, parking, main business zone, lighting, greenery and customer-friendly layout.'
    ];
}


function buildScenarioComparison(string $idea, float $budget, array $income): array
{
    $baseRevenue = (float)($income['average_monthly_revenue'] ?? max(120000, $budget * 0.12));
    $lowInv = max(500000, min($budget * 0.35, 1200000));
    $stdInv = max($lowInv + 300000, min($budget * 0.65, 3000000));
    $premiumInv = max($stdInv + 700000, max($budget, 5000000));
    return [
        [
            'plan' => 'Starter Plan',
            'investment' => round($lowInv),
            'monthly_profit' => round($baseRevenue * 0.22),
            'risk' => 'Low',
            'best_for' => 'Testing demand with minimum facilities',
            'focus' => 'Clean land, sign board, parking, basic seating, one income stream'
        ],
        [
            'plan' => 'Standard Plan',
            'investment' => round($stdInv),
            'monthly_profit' => round($baseRevenue * 0.38),
            'risk' => 'Medium',
            'best_for' => 'Running a serious small business',
            'focus' => 'Core business area, washrooms, customer comfort, packages, marketing'
        ],
        [
            'plan' => 'Premium Plan',
            'investment' => round($premiumInv),
            'monthly_profit' => round($baseRevenue * 0.55),
            'risk' => 'Medium',
            'best_for' => 'Building a branded destination business',
            'focus' => 'Premium design, stronger branding, multiple income streams, online booking'
        ],
    ];
}

function buildProjectAnalyticsSignals(string $type, string $idea, float $budget, array $scores, array $riskHeatmap): array
{
    $overall = $scores[0]['score'] ?? 0;
    $riskCount = 0;
    foreach ($riskHeatmap as $r) if (($r['level'] ?? '') === 'High') $riskCount++;
    return [
        'land_type' => $type,
        'recommended_business' => $idea,
        'budget_band' => $budget > 4500000 ? 'Premium' : ($budget > 2000000 ? 'Standard' : ($budget > 700000 ? 'Starter' : 'Micro')),
        'overall_score' => $overall,
        'high_risk_count' => $riskCount,
    ];
}

?>
