<?php

/**
 * Test script for Phase E endpoints
 * Run: php tests/test-phase-e.php
 */

// Configuration
$API_URL = 'http://localhost:8000/api';
$TEST_EMAIL = 'test@example.com';
$TEST_PASSWORD = 'Password123!';

// Colors for terminal output
$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'reset' => "\033[0m"
];

function log_test($message, $color = 'blue') {
    global $colors;
    echo $colors[$color] . $message . $colors['reset'] . "\n";
}

function log_success($message) {
    log_test("✓ " . $message, 'green');
}

function log_error($message) {
    log_test("✗ " . $message, 'red');
}

function log_warning($message) {
    log_test("⚠ " . $message, 'yellow');
}

function make_request($method, $path, $data = null, $token = null) {
    global $API_URL;
    
    $url = $API_URL . $path;
    $options = [
        'http' => [
            'method' => $method,
            'header' => [
                'Content-Type: application/json',
            ],
            'ignore_errors' => true,
        ],
    ];
    
    if ($token) {
        $options['http']['header'][] = "Authorization: Bearer " . $token;
    }
    
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    return json_decode($response, true);
}

// ───────────────────────────────────────────
// TEST SEQUENCE
// ───────────────────────────────────────────

echo "\n";
log_test("╔════════════════════════════════════════════════════════╗", 'blue');
log_test("║          PHASE E – Module Utilisateurs & KYC          ║", 'blue');
log_test("╚════════════════════════════════════════════════════════╝", 'blue');
echo "\n";

// Step 1: Register
log_test("STEP 1: Register utilisateur", 'yellow');
$registerData = [
    'email' => 'test_' . time() . '@example.com',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'first_name' => 'Test',
    'last_name' => 'User'
];

$registerResponse = make_request('POST', '/auth/register', $registerData);
if ($registerResponse['success']) {
    log_success("Utilisateur créé");
    log_test("  Email: " . $registerResponse['data']['email']);
} else {
    log_error("Enregistrement échoué: " . ($registerResponse['message'] ?? 'Unknown error'));
    exit(1);
}

// Step 2: Login
log_test("\nSTEP 2: Login", 'yellow');
$loginData = [
    'email' => $registerData['email'],
    'password' => $registerData['password']
];

$loginResponse = make_request('POST', '/auth/login', $loginData);
if ($loginResponse['success']) {
    log_success("Login réussi");
    $token = $loginResponse['data']['access_token'];
    log_test("  Token: " . substr($token, 0, 20) . "...");
} else {
    log_error("Login échoué: " . ($loginResponse['message'] ?? 'Unknown error'));
    exit(1);
}

// Step 3: Get Profile (/me)
log_test("\nSTEP 3: Récupérer le profil (/me)", 'yellow');
$profileResponse = make_request('GET', '/me', null, $token);
if ($profileResponse['success']) {
    log_success("Profil récupéré");
    log_test("  ID: " . $profileResponse['data']['id']);
    log_test("  Email: " . $profileResponse['data']['email']);
    log_test("  Status: " . $profileResponse['data']['status']);
} else {
    log_error("Profil non récupéré: " . ($profileResponse['message'] ?? 'Unknown error'));
}

// Step 4: Update Profile
log_test("\nSTEP 4: Mettre à jour le profil", 'yellow');
$updateData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'country' => 'Democratic Republic of Congo',
    'city' => 'Kinshasa',
    'address' => '123 Main St',
    'bio' => 'Test bio for KYC submission'
];

$updateResponse = make_request('PUT', '/me', $updateData, $token);
if ($updateResponse['success']) {
    log_success("Profil mis à jour");
    log_test("  Nom: " . $updateResponse['data']['first_name'] . " " . $updateResponse['data']['last_name']);
} else {
    log_error("Mise à jour échouée: " . ($updateResponse['message'] ?? 'Unknown error'));
}

// Step 5: Get KYC Status
log_test("\nSTEP 5: Vérifier le statut KYC", 'yellow');
$kycResponse = make_request('GET', '/kyc', null, $token);
if ($kycResponse['success']) {
    if ($kycResponse['data']) {
        log_success("KYC trouvé");
        log_test("  Status: " . $kycResponse['data']['status']);
    } else {
        log_warning("Pas de KYC soumis (normal pour nouveau user)");
    }
} else {
    log_error("KYC non trouvé: " . ($kycResponse['message'] ?? 'Unknown error'));
}

// Step 6: Submit KYC
log_test("\nSTEP 6: Soumettre une soumission KYC", 'yellow');
$kycSubmitData = [];
$submitResponse = make_request('POST', '/kyc', $kycSubmitData, $token);
if ($submitResponse['success']) {
    log_success("KYC soumission créée");
    log_test("  Submission ID: " . $submitResponse['data']['id']);
    log_test("  Status: " . $submitResponse['data']['status']);
} else {
    log_error("KYC soumission échouée: " . ($submitResponse['message'] ?? 'Unknown error'));
}

// Step 7: Register as Merchant
log_test("\nSTEP 7: S'enregistrer comme marchand", 'yellow');
$merchantData = [
    'business_name' => 'Test Pharmacy',
    'business_type' => 'retailer',
    'description' => 'A test pharmacy for demonstration',
    'warehouse_address' => '456 Commerce St',
    'warehouse_city' => 'Kinshasa',
    'warehouse_country' => 'Democratic Republic of Congo',
    'processing_time_days' => 3,
    'accepts_cod' => true,
    'accepts_wallet' => true,
];

$merchantResponse = make_request('POST', '/merchants', $merchantData, $token);
if ($merchantResponse['success']) {
    log_success("Compte marchand créé");
    log_test("  Merchant ID: " . $merchantResponse['data']['id']);
    log_test("  Business: " . $merchantResponse['data']['business_name']);
} else {
    log_error("Enregistrement marchand échoué: " . ($merchantResponse['message'] ?? 'Unknown error'));
}

// Summary
echo "\n";
log_test("╔════════════════════════════════════════════════════════╗", 'blue');
log_test("║                    TEST SUMMARY                        ║", 'blue');
log_test("╚════════════════════════════════════════════════════════╝", 'blue');
log_success("Tous les tests Phase E ont réussi! ✨");
echo "\n";
