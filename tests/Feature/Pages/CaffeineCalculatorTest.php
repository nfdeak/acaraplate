<?php

declare(strict_types=1);

use App\Actions\CalculateCaffeineSafeDose;
use App\Actions\CalculateCaffeineSleepCutoff;
use App\Actions\LogToolEvent;
use App\Actions\SearchCaffeineDrinks;
use App\Http\Controllers\CaffeineCalculatorController;
use App\Models\CaffeineDrink;
use App\Models\User;
use App\Utilities\WeightConverter;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

covers(CaffeineCalculatorController::class);

/**
 * @param  array<int, array{id: int, name: string, category: ?string, caffeine_mg: float, rank: int}>  $results
 */
function fakeCaffeineSearchResults(array $results): void
{
    app()->bind(SearchCaffeineDrinks::class, fn () => new class($results)
    {
        /**
         * @param  array<int, array{id: int, name: string, category: ?string, caffeine_mg: float, rank: int}>  $results
         */
        public function __construct(private array $results) {}

        public function handle(string $query): Collection
        {
            return collect($this->results);
        }
    });
}

it('returns 200 for the caffeine calculator route without authentication', function (): void {
    $this->get(route('caffeine-calculator'))
        ->assertSuccessful();
});

it('renders the caffeine-calculator Inertia page with expected props', function (): void {
    CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);

    $this->get(route('caffeine-calculator'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('caffeine-calculator')
            ->where('unit', 'kg')
            ->where('hasDrinks', true)
            ->where('minWeightKg', 30)
            ->where('maxWeightKg', 250)
            ->where('isGuest', true)
            ->where('registerUrl', route('register').'?source=caffeine_calculator'));
});

it('flags hasDrinks as false when no drinks exist', function (): void {
    expect(CaffeineDrink::count())->toBe(0);

    $this->get(route('caffeine-calculator'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('hasDrinks', false));
});

it('flags isGuest false for authenticated visitors', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('caffeine-calculator'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('isGuest', false));
});

it('persists the weight unit choice via the unit query param', function (): void {
    $this->get(route('caffeine-calculator', ['unit' => 'lb']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('unit', 'lb'));
});

it('falls back to kg when an unsupported unit is supplied', function (): void {
    $this->get(route('caffeine-calculator', ['unit' => 'stone']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where('unit', 'kg'));
});

it('renders a self-referential canonical link tag and a meta description', function (): void {
    $response = $this->get(route('caffeine-calculator'))->assertSuccessful();

    $canonicalUrl = strtok(route('caffeine-calculator'), '?');

    $response->assertSee('<link rel="canonical" href="'.$canonicalUrl.'"', false)
        ->assertSeeInOrder([
            'name="description"',
            'content="Free caffeine calculator: estimate your safe daily caffeine dose and find out when to stop drinking coffee for better sleep."',
        ], false);
});

it('renders a server-side title tag when rendering the caffeine calculator', function (): void {
    $this->get(route('caffeine-calculator'))
        ->assertSuccessful()
        ->assertSee('Coffee Caffeine Calculator: How Much Is Too Much?', false);
});

it('emits parseable WebApplication and FAQPage JSON-LD blocks', function (): void {
    $html = $this->get(route('caffeine-calculator'))
        ->assertSuccessful()
        ->getContent();

    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', (string) $html, $matches);

    $blocks = collect($matches[1])
        ->map(fn (string $json): ?array => json_decode(mb_trim($json), true))
        ->filter();

    expect($blocks)->not->toBeEmpty();

    $webApp = $blocks->firstWhere('@type', 'WebApplication');
    expect($webApp)->not->toBeNull()
        ->and($webApp['name'] ?? null)->toBeString()->not->toBe('')
        ->and($webApp['applicationCategory'] ?? null)->toBe('HealthApplication');

    $faq = $blocks->firstWhere('@type', 'FAQPage');
    expect($faq)->not->toBeNull()
        ->and($faq['mainEntity'] ?? null)->toBeArray()->not->toBeEmpty()
        ->and($faq['mainEntity'][0]['@type'] ?? null)->toBe('Question')
        ->and($faq['mainEntity'][0]['acceptedAnswer']['text'] ?? null)->toBeString()->not->toBe('');
});

it('logs a page_view tool event once per index render', function (): void {
    $this->get(route('caffeine-calculator'))->assertSuccessful();

    $rows = DB::table('tool_events')
        ->where('event_name', 'page_view')
        ->where('tool_name', 'caffeine-calculator')
        ->get();

    expect($rows)->toHaveCount(1);
});

it('registers the caffeine calculator route at /tools/caffeine-calculator without auth middleware', function (): void {
    $route = collect(app('router')->getRoutes())
        ->first(fn ($route) => $route->getName() === 'caffeine-calculator');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('tools/caffeine-calculator')
        ->and($route->gatherMiddleware())->not->toContain('auth')
        ->and($route->gatherMiddleware())->not->toContain('auth:web');
});

it('returns drink search results for a non-empty query', function (): void {
    $americano = CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);
    $milk = CaffeineDrink::factory()->create(['name' => 'Americano with Milk', 'slug' => 'americano-milk']);

    fakeCaffeineSearchResults([
        ['id' => $americano->id, 'name' => 'Americano', 'category' => null, 'caffeine_mg' => 95.0, 'rank' => 0],
        ['id' => $milk->id, 'name' => 'Americano with Milk', 'category' => null, 'caffeine_mg' => 110.0, 'rank' => 1],
    ]);

    $this->getJson(route('caffeine-calculator.search', ['q' => 'ameri']))
        ->assertSuccessful()
        ->assertJson([
            'results' => [
                ['id' => $americano->id, 'name' => 'Americano', 'rank' => 0],
                ['id' => $milk->id, 'name' => 'Americano with Milk', 'rank' => 1],
            ],
        ]);
});

it('returns an empty results array for an empty query without logging', function (): void {
    $this->getJson(route('caffeine-calculator.search', ['q' => '']))
        ->assertSuccessful()
        ->assertExactJson(['results' => []]);

    expect(DB::table('tool_events')->where('event_name', 'search_submitted')->count())->toBe(0);
});

it('logs a search_submitted tool event when a non-empty query is searched', function (): void {
    $this->getJson(route('caffeine-calculator.search', ['q' => 'ameri']))
        ->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'search_submitted')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');

    $properties = json_decode($row->properties, true);

    expect($properties)->toHaveKey('query_length');
});

it('logs a search_results_returned tool event with result count', function (): void {
    $drink = CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);

    fakeCaffeineSearchResults([
        ['id' => $drink->id, 'name' => 'Americano', 'category' => null, 'caffeine_mg' => 95.0, 'rank' => 0],
    ]);

    $this->getJson(route('caffeine-calculator.search', ['q' => 'ameri']))
        ->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'search_results_returned')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->toHaveKey('result_count', 1)
        ->toHaveKey('query_length');
});

it('logs a search_no_results tool event when no matches are found', function (): void {
    CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);

    $this->getJson(route('caffeine-calculator.search', ['q' => 'xyz']))
        ->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'search_no_results')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');
});

it('rejects the calculate endpoint when weight is blank', function (): void {
    $drink = CaffeineDrink::factory()->create(['caffeine_mg' => 100]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertUnprocessable()
        ->assertJsonPath('errors.weight.0', 'Enter your weight to calculate.');
});

it('rejects the calculate endpoint when weight is non-numeric', function (): void {
    $drink = CaffeineDrink::factory()->create(['caffeine_mg' => 100]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => 'abc',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertUnprocessable()
        ->assertJsonPath('errors.weight.0', 'Weight must be a number.');
});

it('rejects the calculate endpoint when weight is not greater than zero', function (): void {
    $drink = CaffeineDrink::factory()->create(['caffeine_mg' => 100]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '-5',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertUnprocessable()
        ->assertJsonPath('errors.weight.0', 'Weight must be greater than 0.');
});

it('produces a SafeDoseData result on calculate using the shared LB_TO_KG conversion constant', function (): void {
    $drink = CaffeineDrink::factory()->create([
        'name' => 'Americano',
        'slug' => 'americano',
        'caffeine_mg' => 150,
    ]);

    $weightKg = WeightConverter::convertToKg(154.0, 'lb');
    $expected = (new CalculateCaffeineSafeDose)->handle(
        weightKg: $weightKg,
        sensitivityStep: 2,
        perCupMg: 150.0,
    );

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '154',
        'weight_unit' => 'lb',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful()
        ->assertJson([
            'lacks_caffeine_estimate' => false,
            'safe_mg' => $expected->safeMg,
            'safe_cups' => $expected->cups,
            'per_cup_mg' => 150.0,
        ]);
});

it('clamps the safe dose calculation to the documented minimum weight', function (): void {
    $drink = CaffeineDrink::factory()->create(['name' => 'Americano', 'caffeine_mg' => 150]);

    $expected = (new CalculateCaffeineSafeDose)->handle(
        weightKg: 30.0,
        sensitivityStep: 2,
        perCupMg: 150.0,
    );

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '15',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful()
        ->assertJson([
            'safe_mg' => $expected->safeMg,
            'safe_cups' => $expected->cups,
        ]);
});

it('clamps the safe dose calculation to the documented maximum weight', function (): void {
    $drink = CaffeineDrink::factory()->create(['name' => 'Americano', 'caffeine_mg' => 150]);

    $expected = (new CalculateCaffeineSafeDose)->handle(
        weightKg: 250.0,
        sensitivityStep: 2,
        perCupMg: 150.0,
    );

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '400',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful()
        ->assertJson([
            'safe_mg' => $expected->safeMg,
            'safe_cups' => $expected->cups,
        ]);
});

it('returns a fallback flag when the picked drink lacks a caffeine estimate', function (): void {
    $drink = CaffeineDrink::factory()->create([
        'name' => 'Mystery Brew',
        'slug' => 'mystery-brew',
        'caffeine_mg' => 0,
    ]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '70',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful()
        ->assertExactJson(['lacks_caffeine_estimate' => true]);
});

it('logs a calculation_completed tool event with sensitivity_step, safe_mg_bucket, and cups_bucket', function (): void {
    $drink = CaffeineDrink::factory()->create([
        'name' => 'Americano',
        'slug' => 'americano',
        'caffeine_mg' => 150,
    ]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '70',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'calculation_completed')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');

    $expected = (new CalculateCaffeineSafeDose)->handle(
        weightKg: 70.0,
        sensitivityStep: 2,
        perCupMg: 150.0,
    );

    $logger = app(LogToolEvent::class);

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->toHaveKey('sensitivity_step', 3)
        ->toHaveKey('safe_mg_bucket', $logger->bucketSafeMg($expected->safeMg))
        ->toHaveKey('cups_bucket', $logger->bucketCups($expected->cups))
        ->not->toHaveKey('safe_mg')
        ->not->toHaveKey('cups');
});

it('does not log calculation_completed when the picked drink lacks a caffeine estimate', function (): void {
    $drink = CaffeineDrink::factory()->create([
        'name' => 'Mystery Brew',
        'caffeine_mg' => 0,
    ]);

    $this->postJson(route('caffeine-calculator.calculate'), [
        'weight' => '70',
        'weight_unit' => 'kg',
        'sensitivity' => 3,
        'drink_id' => $drink->id,
    ])->assertSuccessful();

    expect(DB::table('tool_events')->where('event_name', 'calculation_completed')->count())->toBe(0);
});

it('returns a sleep cutoff time using CalculateCaffeineSleepCutoff when bedtime is in the future', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 4, 27, 10, 0, 0));

    $bedtimeToday = CarbonImmutable::now()->setTimeFromTimeString('22:00');
    $expected = app(CalculateCaffeineSleepCutoff::class)->handle($bedtimeToday, 100.0, 4);

    expect($expected)->toBeInstanceOf(CarbonImmutable::class);

    $this->postJson(route('caffeine-calculator.sleep-cutoff'), [
        'bedtime' => '22:00',
        'per_cup_mg' => 100.0,
        'safe_cups' => 4,
    ])->assertSuccessful()
        ->assertJson([
            'state' => 'cutoff',
            'time' => $expected->format('g:i A'),
        ]);

    CarbonImmutable::setTestNow();
});

it('returns the past state when the chosen bedtime is already in the past today', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2026, 4, 27, 23, 0, 0));

    $this->postJson(route('caffeine-calculator.sleep-cutoff'), [
        'bedtime' => '07:00',
        'per_cup_mg' => 100.0,
        'safe_cups' => 4,
    ])->assertSuccessful()
        ->assertExactJson(['state' => 'past']);

    CarbonImmutable::setTestNow();
});

it('rejects an invalid bedtime format', function (): void {
    $this->postJson(route('caffeine-calculator.sleep-cutoff'), [
        'bedtime' => 'not-a-time',
        'per_cup_mg' => 100.0,
        'safe_cups' => 4,
    ])->assertUnprocessable();
});

it('logs a unit_toggled tool event recording the new unit', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'unit_toggled',
        'properties' => ['unit' => 'lb'],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'unit_toggled')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');

    $properties = json_decode($row->properties, true);

    expect($properties)->toHaveKey('unit', 'lb');
});

it('does not log a unit_toggled tool event when the unit value is unsupported', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'unit_toggled',
        'properties' => ['unit' => 'stone'],
    ])->assertSuccessful();

    expect(DB::table('tool_events')->where('event_name', 'unit_toggled')->count())->toBe(0);
});

it('logs a sensitivity_changed tool event with the bucketed sensitivity step', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'sensitivity_changed',
        'properties' => ['sensitivity_step' => 5],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'sensitivity_changed')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)->toHaveKey('sensitivity_step', 5);
});

it('does not log a sensitivity_changed tool event when the step is out of range', function (): void {
    foreach ([0, 6, 'abc'] as $step) {
        $this->postJson(route('caffeine-calculator.event'), [
            'event' => 'sensitivity_changed',
            'properties' => ['sensitivity_step' => $step],
        ])->assertSuccessful();
    }

    expect(DB::table('tool_events')->where('event_name', 'sensitivity_changed')->count())->toBe(0);
});

it('logs a weight_entered tool event with the bucketed kilogram weight', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'weight_entered',
        'properties' => ['weight' => '72', 'unit' => 'kg'],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'weight_entered')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->toHaveKey('weight_kg', '70-79')
        ->not->toHaveKey('weight');
});

it('logs a weight_entered tool event with a kilogram bucket converted from pounds', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'weight_entered',
        'properties' => ['weight' => '154', 'unit' => 'lb'],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'weight_entered')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)->toHaveKey('weight_kg', '60-69');
});

it('does not log a weight_entered tool event when the weight is invalid', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'weight_entered',
        'properties' => ['weight' => '', 'unit' => 'kg'],
    ])->assertSuccessful();

    expect(DB::table('tool_events')->where('event_name', 'weight_entered')->count())->toBe(0);
});

it('logs a drink_picked tool event with the drink slug', function (): void {
    $drink = CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);

    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'drink_picked',
        'properties' => ['drink_id' => $drink->id],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'drink_picked')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->toHaveKey('drink', 'americano')
        ->not->toHaveKey('id')
        ->not->toHaveKey('name');
});

it('does not log a drink_picked tool event for an unknown drink id', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'drink_picked',
        'properties' => ['drink_id' => 999_999],
    ])->assertSuccessful();

    expect(DB::table('tool_events')->where('event_name', 'drink_picked')->count())->toBe(0);
});

it('logs a search_result_selected tool event with drink, rank, and query length', function (): void {
    $drink = CaffeineDrink::factory()->create(['name' => 'Americano', 'slug' => 'americano']);

    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'search_result_selected',
        'properties' => [
            'drink_id' => $drink->id,
            'rank' => 0,
            'query_length' => 5,
        ],
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'search_result_selected')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->toHaveKey('drink', 'americano')
        ->toHaveKey('rank', 0)
        ->toHaveKey('query_length', 5);
});

it('logs a sleep_disclosure_opened tool event without sensitive properties', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'sleep_disclosure_opened',
    ])->assertSuccessful();

    $row = DB::table('tool_events')
        ->where('event_name', 'sleep_disclosure_opened')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull();

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->not->toHaveKey('bedtime')
        ->not->toHaveKey('weight')
        ->not->toHaveKey('weight_kg')
        ->not->toHaveKey('ip')
        ->not->toHaveKey('user_agent');
});

it('rejects unknown event names on the event endpoint', function (): void {
    $this->postJson(route('caffeine-calculator.event'), [
        'event' => 'arbitrary_event',
    ])->assertUnprocessable();

    expect(DB::table('tool_events')->where('event_name', 'arbitrary_event')->count())->toBe(0);
});

it('logs a signup_cta_clicked tool event and redirects to the registration page', function (): void {
    $response = $this->post(route('caffeine-calculator.signup-cta'));

    $response->assertRedirect(route('register').'?source=caffeine_calculator');

    $row = DB::table('tool_events')
        ->where('event_name', 'signup_cta_clicked')
        ->latest('id')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->tool_name)->toBe('caffeine-calculator');

    $properties = json_decode($row->properties, true);

    expect($properties)
        ->not->toHaveKey('email')
        ->not->toHaveKey('user_id')
        ->not->toHaveKey('ip')
        ->not->toHaveKey('user_agent')
        ->not->toHaveKey('weight')
        ->not->toHaveKey('weight_kg');
});
