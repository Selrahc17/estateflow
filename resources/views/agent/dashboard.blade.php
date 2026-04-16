<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">EstateFlow</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ $user->name }}</span>
                        <span class="text-sm text-gray-500">Agent</span>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Profile</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Agent Dashboard</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Properties -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Properties</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Properties</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Available</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Reserved</span>
                                <span class="font-semibold">0</span>
                            </div>
                        </div>
                        <a href="{{ route('agent.properties') }}" class="block mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">Manage Properties</a>
                    </div>

                    <!-- Reservations -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reservations</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Reservations</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pending</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Completed</span>
                                <span class="font-semibold">0</span>
                            </div>
                        </div>
                        <a href="{{ route('agent.reservations') }}" class="block mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-center">Manage Reservations</a>
                    </div>

                    <!-- Clients -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Clients</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Clients</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Clients</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">New Clients</span>
                                <span class="font-semibold">0</span>
                            </div>
                        </div>
                        <a href="#" class="block mt-4 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-center">Manage Clients</a>
                    </div>

                    <!-- Performance -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Performance</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sales</span>
                                <span class="font-semibold">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Commissions</span>
                                <span class="font-semibold">$0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rating</span>
                                <span class="font-semibold">0/5</span>
                            </div>
                        </div>
                        <a href="#" class="block mt-4 bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 text-center">View Performance</a>
                    </div>
                </div>

                <!-- Sidebar Navigation -->
                <div class="hidden lg:block lg:col-span-1">
                    <div class="sticky top-4 space-y-1">
                        <div class="list-group">
                            <a href="{{ route('agent.dashboard') }}" class="list-group-item list-group-item-action">Dashboard</a>
                            <a href="{{ route('property-types.index') }}" class="list-group-item list-group-item-action">Property Types</a>
                            <a href="{{ route('agent.properties') }}" class="list-group-item list-group-item-action">Properties</a>
                            <a href="{{ route('agent.reservations') }}" class="list-group-item list-group-item-action">Reservations</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>