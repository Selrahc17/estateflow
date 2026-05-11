<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PropertyType;
use App\Models\Property;
use App\Models\Client;
use App\Models\Agent;
use App\Models\Staff;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Budget;
use App\Models\ProgressLog;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create(['name' => 'Admin User', 'email' => 'admin@estateflow.com', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]);
        $agentUser = User::create(['name' => 'Juan dela Cruz', 'email' => 'agent@estateflow.com', 'password' => Hash::make('password'), 'role' => 'agent', 'is_active' => true]);
        $financeUser = User::create(['name' => 'Rosa Mendoza', 'email' => 'finance@estateflow.com', 'password' => Hash::make('password'), 'role' => 'finance', 'is_active' => true]);
        $contractorUser = User::create(['name' => 'Pedro Santos', 'email' => 'contractor@estateflow.com', 'password' => Hash::make('password'), 'role' => 'staff', 'is_active' => true]);
        $clientUser = User::create(['name' => 'Maria Reyes', 'email' => 'client@estateflow.com', 'password' => Hash::make('password'), 'role' => 'client', 'is_active' => true]);

        // Property Types
        $lot = PropertyType::create(['name' => 'Lot', 'description' => 'Residential lot for sale', 'is_active' => true]);
        $house = PropertyType::create(['name' => 'House & Lot', 'description' => 'House with lot included', 'is_active' => true]);
        $condo = PropertyType::create(['name' => 'Condominium', 'description' => 'Condo unit', 'is_active' => true]);
        $commercial = PropertyType::create(['name' => 'Commercial', 'description' => 'Commercial property', 'is_active' => true]);

        // Properties
        $p1 = Property::create([
            'property_type_id' => $house->id, 'title' => 'Modern House in Quezon City',
            'description' => 'Beautiful 3-bedroom house with garden.', 'location' => 'Quezon City, Metro Manila',
            'latitude' => 14.6760, 'longitude' => 121.0437, 'area_sqm' => 120.00,
            'price' => 5500000, 'currency' => 'PHP', 'status' => 'available',
            'bedrooms' => 3, 'bathrooms' => 2, 'garage_spaces' => 1,
            'amenities' => ['garden', 'garage', 'security'], 'is_featured' => true, 'is_active' => true,
        ]);
        $p2 = Property::create([
            'property_type_id' => $condo->id, 'title' => 'BGC Condo Unit 2BR',
            'description' => 'High-rise condo in Bonifacio Global City.', 'location' => 'BGC, Taguig City',
            'latitude' => 14.5547, 'longitude' => 121.0509, 'area_sqm' => 65.00,
            'price' => 8200000, 'currency' => 'PHP', 'status' => 'available',
            'bedrooms' => 2, 'bathrooms' => 1, 'garage_spaces' => 1,
            'amenities' => ['pool', 'gym', 'concierge'], 'is_featured' => true, 'is_active' => true,
        ]);
        $p3 = Property::create([
            'property_type_id' => $lot->id, 'title' => 'Residential Lot in Cavite',
            'description' => '200sqm lot in a gated subdivision.', 'location' => 'Bacoor, Cavite',
            'latitude' => 14.4624, 'longitude' => 120.9645, 'area_sqm' => 200.00,
            'price' => 2800000, 'currency' => 'PHP', 'status' => 'reserved',
            'is_featured' => false, 'is_active' => true,
        ]);
        $p4 = Property::create([
            'property_type_id' => $commercial->id, 'title' => 'Commercial Space Makati',
            'description' => 'Ground floor commercial space ideal for retail.', 'location' => 'Makati City',
            'latitude' => 14.5547, 'longitude' => 121.0244, 'area_sqm' => 85.00,
            'price' => 12000000, 'currency' => 'PHP', 'status' => 'available',
            'is_featured' => false, 'is_active' => true,
        ]);

        // Agents
        $agent = Agent::create([
            'user_id' => $agentUser->id, 'first_name' => 'Juan', 'last_name' => 'dela Cruz',
            'email' => 'agent@estateflow.com', 'phone' => '09171234567',
            'license_number' => 'PRC-RE-12345', 'commission_rate' => 3.00, 'status' => 'active',
        ]);

        // Clients
        $client = Client::create([
            'user_id' => $clientUser->id, 'first_name' => 'Maria', 'last_name' => 'Reyes',
            'email' => 'client@estateflow.com', 'phone' => '09189876543',
            'address' => '123 Rizal St, Pasig City', 'id_type' => 'Passport',
            'id_number' => 'P1234567A', 'status' => 'active',
        ]);

        // Staff
        $contractor = Staff::create([
            'user_id' => $contractorUser->id, 'company_name' => 'Santos Construction Corp.',
            'contact_person' => 'Pedro Santos', 'email' => 'contractor@estateflow.com',
            'phone' => '09201112222', 'license_number' => 'PCAB-12345',
            'type' => 'general_contractor', 'specialization' => 'Residential and commercial construction',
            'status' => 'active',
        ]);

        // Reservations
        $reservation = Reservation::create([
            'property_id' => $p3->id, 'client_id' => $client->id, 'agent_id' => $agent->id,
            'reservation_date' => '2026-04-01', 'expiry_date' => '2026-05-01',
            'reservation_fee' => 50000, 'status' => 'confirmed',
            'notes' => 'Client prefers early turnover.',
        ]);

        // Payments
        Payment::create([
            'reservation_id' => $reservation->id, 'client_id' => $client->id, 'agent_id' => $agent->id,
            'payment_type' => 'reservation', 'amount' => 50000, 'currency' => 'PHP',
            'payment_method' => 'bank_transfer', 'reference_number' => 'BT-20260401-001',
            'payment_date' => '2026-04-01', 'status' => 'completed',
            'description' => 'Reservation fee for Cavite lot.',
        ]);

        // Projects
        $project = Project::create([
            'name' => 'Cavite Lot House Construction',
            'description' => 'Construction of a 2-storey house on the reserved Cavite lot.',
            'property_id' => $p3->id, 'client_id' => $client->id, 'staff_id' => $contractor->id,
            'start_date' => '2026-05-01', 'estimated_completion_date' => '2026-11-01',
            'budget' => 3500000, 'actual_cost' => 450000,
            'status' => 'in_progress', 'completion_percentage' => 15,
        ]);

        // Tasks
        Task::create([
            'project_id' => $project->id, 'title' => 'Site Clearing & Excavation',
            'description' => 'Clear the lot and excavate for foundation.',
            'assigned_to' => $contractorUser->id, 'assigned_by' => $admin->id,
            'start_date' => '2026-05-01', 'due_date' => '2026-05-15',
            'completed_date' => '2026-05-14', 'priority' => 'high',
            'status' => 'completed', 'estimated_hours' => 80, 'actual_hours' => 75,
        ]);
        Task::create([
            'project_id' => $project->id, 'title' => 'Foundation Pouring',
            'description' => 'Pour concrete foundation.',
            'assigned_to' => $contractorUser->id, 'assigned_by' => $admin->id,
            'start_date' => '2026-05-16', 'due_date' => '2026-06-01',
            'priority' => 'high', 'status' => 'in_progress',
            'estimated_hours' => 120,
        ]);
        Task::create([
            'project_id' => $project->id, 'title' => 'Framing & Roofing',
            'description' => 'Structural framing and roof installation.',
            'assigned_to' => $contractorUser->id, 'assigned_by' => $admin->id,
            'start_date' => '2026-06-02', 'due_date' => '2026-07-15',
            'priority' => 'medium', 'status' => 'pending',
            'estimated_hours' => 200,
        ]);

        // Milestones
        Milestone::create([
            'project_id' => $project->id, 'name' => 'Foundation Complete',
            'description' => 'Foundation fully poured and cured.',
            'target_date' => '2026-06-01', 'is_completed' => false, 'completion_percentage' => 60,
        ]);
        Milestone::create([
            'project_id' => $project->id, 'name' => 'Structural Completion',
            'description' => 'All structural work done.',
            'target_date' => '2026-08-01', 'is_completed' => false, 'completion_percentage' => 0,
        ]);

        // Budgets
        Budget::create([
            'project_id' => $project->id, 'category' => 'materials',
            'description' => 'Cement, steel, lumber and other materials.',
            'estimated_amount' => 1800000, 'actual_amount' => 250000,
            'currency' => 'PHP', 'budget_date' => '2026-05-01', 'status' => 'in_progress',
        ]);
        Budget::create([
            'project_id' => $project->id, 'category' => 'labor',
            'description' => 'Construction workers and skilled labor.',
            'estimated_amount' => 1200000, 'actual_amount' => 180000,
            'currency' => 'PHP', 'budget_date' => '2026-05-01', 'status' => 'in_progress',
        ]);
        Budget::create([
            'project_id' => $project->id, 'category' => 'permits',
            'description' => 'Building permits and government fees.',
            'estimated_amount' => 80000, 'actual_amount' => 75000,
            'currency' => 'PHP', 'budget_date' => '2026-05-01', 'status' => 'completed',
        ]);

        // Progress Logs
        ProgressLog::create([
            'project_id' => $project->id, 'user_id' => $contractorUser->id,
            'log_date' => '2026-05-14', 'description' => 'Site clearing completed. Excavation done to required depth.',
            'completion_percentage' => 10, 'workers_count' => 8, 'hours_worked' => 75,
            'weather_conditions' => 'Sunny, no delays.',
        ]);
        ProgressLog::create([
            'project_id' => $project->id, 'user_id' => $contractorUser->id,
            'log_date' => '2026-05-20', 'description' => 'Foundation reinforcement steel installed. Concrete pouring started.',
            'completion_percentage' => 15, 'workers_count' => 10, 'hours_worked' => 40,
            'weather_conditions' => 'Partly cloudy.',
        ]);
    }
}
