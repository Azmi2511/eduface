<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$username = 'admin';
$password = 'password';

$existing = User::where('username', $username)->first();
if ($existing) {
    echo "User already exists: {$username}\n";
    exit(0);
}

User::create([
    'username' => $username,
    'password' => Hash::make($password),
    'full_name' => 'Admin User',
    'role' => 'admin',
    'is_active' => true,
    'email' => 'admin@example.com',
]);

echo "Created user {$username} with password {$password}\n";
