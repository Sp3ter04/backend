<?php

/**
 * Test Route UUID Constraint
 * 
 * This script tests if the UUID constraint on the exercises route works correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "🧪 Testing Route UUID Constraint\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the router
$router = app('router');
$routes = $router->getRoutes();

// Find the exercises.show route
$exerciseShowRoute = null;
foreach ($routes as $route) {
    if ($route->getName() === 'exercises.show') {
        $exerciseShowRoute = $route;
        break;
    }
}

if (!$exerciseShowRoute) {
    echo "❌ Route 'exercises.show' not found!\n\n";
    exit(1);
}

echo "✅ Route found: {$exerciseShowRoute->uri()}\n";
echo "   Name: {$exerciseShowRoute->getName()}\n";
echo "   Methods: " . implode(', ', $exerciseShowRoute->methods()) . "\n\n";

// Check for UUID constraint
$wheres = $exerciseShowRoute->wheres;
echo "📋 Route Constraints:\n";

if (empty($wheres)) {
    echo "   ⚠️  No constraints defined!\n\n";
    echo "   This means the route will accept ANY string, including:\n";
    echo "   • exercise-1-a-mae.mp3\n";
    echo "   • anything-at-all.txt\n";
    echo "   • 12345\n\n";
} else {
    foreach ($wheres as $param => $pattern) {
        echo "   • Parameter: {$param}\n";
        echo "     Pattern: {$pattern}\n";
    }
    echo "\n";
    
    if (isset($wheres['exercise'])) {
        $pattern = $wheres['exercise'];
        
        // Check if it's a UUID pattern
        if (strpos($pattern, 'uuid') !== false || strpos($pattern, '[0-9a-f]{8}-[0-9a-f]{4}') !== false) {
            echo "✅ UUID constraint is ACTIVE\n\n";
            echo "   This route will ONLY match valid UUIDs like:\n";
            echo "   • 550e8400-e29b-41d4-a716-446655440000\n\n";
            echo "   And will REJECT non-UUID strings like:\n";
            echo "   • exercise-1-a-mae.mp3 ❌\n";
            echo "   • 12345 ❌\n\n";
        } else {
            echo "⚠️  Constraint exists but may not be UUID-specific\n";
            echo "   Pattern: {$pattern}\n\n";
        }
    } else {
        echo "⚠️  No constraint on 'exercise' parameter\n\n";
    }
}

// Test matching
echo "🧪 Route Matching Tests:\n";
echo str_repeat("─", 64) . "\n\n";

$testCases = [
    [
        'url' => '/api/exercises/550e8400-e29b-41d4-a716-446655440000',
        'description' => 'Valid UUID',
        'shouldMatch' => true
    ],
    [
        'url' => '/api/exercises/exercise-1-a-mae.mp3',
        'description' => 'Audio filename',
        'shouldMatch' => false
    ],
    [
        'url' => '/api/exercises/12345',
        'description' => 'Plain number',
        'shouldMatch' => false
    ],
    [
        'url' => '/api/exercises/test-slug',
        'description' => 'Text slug',
        'shouldMatch' => false
    ],
];

foreach ($testCases as $test) {
    echo "Test: {$test['description']}\n";
    echo "URL:  {$test['url']}\n";
    
    try {
        $request = \Illuminate\Http\Request::create($test['url'], 'GET');
        $match = $router->getRoutes()->match($request);
        
        if ($match->getName() === 'exercises.show') {
            echo "Result: ✅ MATCHES exercises.show route\n";
            if (!$test['shouldMatch']) {
                echo "⚠️  WARNING: This should NOT have matched!\n";
                echo "   The UUID constraint may not be working.\n";
            }
        } else {
            echo "Result: ❌ Does NOT match (matched: {$match->getName()})\n";
        }
    } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
        echo "Result: ❌ Does NOT match (404)\n";
        if ($test['shouldMatch']) {
            echo "⚠️  WARNING: This SHOULD have matched!\n";
        }
    } catch (\Exception $e) {
        echo "Result: ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 Summary\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!empty($wheres) && isset($wheres['exercise'])) {
    echo "✅ Route constraint is configured\n";
    echo "   Audio files like 'exercise-1-a-mae.mp3' should NOT match\n";
    echo "   this route and will return 404 instead of causing UUID errors.\n";
} else {
    echo "⚠️  Route constraint is NOT configured\n";
    echo "   Action required: Ensure routes/api.php has:\n";
    echo "   Route::apiResource('exercises', ExerciseController::class)\n";
    echo "       ->whereUuid('exercise');\n";
    echo "\n";
    echo "   Then clear route cache: php artisan route:clear\n";
}

echo "\n";
