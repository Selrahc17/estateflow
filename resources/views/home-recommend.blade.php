<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find My Property — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

{{-- Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="/estateflow/public/logo.png" alt="EstateFlow" class="w-8 h-8 object-contain">
            </div>
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('home.browse') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5">Browse</a>
            <a href="{{ route('home.recommend') }}" class="text-sm text-indigo-600 font-semibold px-3 py-1.5">Find My Property</a>
            @guest
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5">Login</a>
                <a href="{{ route('register') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">Register</a>
            @else
                @include('partials.client-nav')
            @endguest
        </div>
    </div>
</nav>

{{-- Hero --}}
<div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-blue-900 text-white py-14 px-6 text-center">
    <div class="max-w-2xl mx-auto">
        <div class="w-14 h-14 bg-white bg-opacity-10 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-brain text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold mb-3">AI Property Recommendation</h1>
        <p class="text-indigo-200 text-sm">Tell us about your preferences and we'll find the best matching properties for you.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Preference Form --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <h2 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-indigo-500"></i> Your Preferences
                </h2>

                <form method="POST" action="{{ route('home.recommend') }}" class="space-y-4">
                    @csrf

                    {{-- Budget --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Budget <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm font-medium">₱</span>
                            <input type="number" name="budget" min="1" step="10000"
                                value="{{ old('budget', $preferences['budget'] ?? '') }}"
                                placeholder="e.g. 2500000" required
                                class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('budget') border-red-400 @enderror">
                        </div>
                        @error('budget')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Enter your maximum budget</p>
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Preferred Location</label>
                        <select name="location"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Any Location</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ ($preferences['location'] ?? '') === $loc ? 'selected' : '' }}>
                                    {{ $loc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Property Type --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">House Model / Property Type</label>
                        <select name="property_type_id"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Any Type</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ ($preferences['property_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Financing --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Financing Option <span class="text-red-500">*</span>
                        </label>
                        <select name="financing" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['cash' => 'Cash', 'bank_loan' => 'Bank Loan', 'pagibig' => 'Pag-IBIG Financing', 'in_house' => 'In-House Financing'] as $val => $label)
                                <option value="{{ $val }}" {{ ($preferences['financing'] ?? 'cash') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Family Size --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Family Size <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([1,2,3,4,5,6,7,8] as $size)
                            <label class="cursor-pointer">
                                <input type="radio" name="family_size" value="{{ $size }}" class="sr-only peer"
                                    {{ ($preferences['family_size'] ?? 2) == $size ? 'checked' : '' }}>
                                <div class="text-center py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600
                                    peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                    hover:border-indigo-300 transition cursor-pointer">
                                    {{ $size }}{{ $size === 8 ? '+' : '' }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Number of family members</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 rounded-xl font-medium text-sm hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-magic"></i> Find My Best Match
                    </button>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="lg:col-span-2">

            @if($recommendations->count())
            {{-- Results Header --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="font-bold text-gray-900 text-lg">{{ $recommendations->count() }} Properties Found</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Ranked by match score based on your preferences</p>
                </div>
                @if($recommendations->first()->match_score >= 85)
                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-star mr-1"></i> Excellent matches found!
                    </span>
                @endif
            </div>

            {{-- Match Score Legend --}}
            <div class="bg-white rounded-xl p-4 mb-5 flex flex-wrap gap-3 text-xs shadow-sm">
                <span class="text-gray-500 font-medium mr-1">Match Score:</span>
                @foreach(['bg-green-500' => '85–100% Excellent', 'bg-blue-500' => '70–84% Great', 'bg-yellow-400' => '55–69% Good', 'bg-orange-400' => '40–54% Fair', 'bg-gray-400' => '<40% Partial'] as $color => $label)
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $color }} inline-block"></span>
                        {{ $label }}
                    </span>
                @endforeach
            </div>

            {{-- Property Cards --}}
            <div class="space-y-4">
                @foreach($recommendations as $index => $property)
                @php
                    $score = $property->match_score;
                    $barColor = $score >= 85 ? 'bg-green-500' : ($score >= 70 ? 'bg-blue-500' : ($score >= 55 ? 'bg-yellow-400' : ($score >= 40 ? 'bg-orange-400' : 'bg-gray-400')));
                    $badgeColor = $score >= 85 ? 'bg-green-100 text-green-700' : ($score >= 70 ? 'bg-blue-100 text-blue-700' : ($score >= 55 ? 'bg-yellow-100 text-yellow-700' : 'bg-orange-100 text-orange-700'));
                @endphp
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">

                    {{-- Match Score Bar --}}
                    <div class="h-1.5 w-full bg-gray-100">
                        <div class="h-1.5 {{ $barColor }} transition-all duration-700" style="width: {{ $score }}%"></div>
                    </div>

                    <div class="flex gap-0">
                        {{-- Property Image --}}
                        <div class="w-40 flex-shrink-0">
                            @if($property->image_main)
                                <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}"
                                    class="w-40 h-full object-cover min-h-32">
                            @else
                                <div class="w-40 h-full min-h-32 bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center">
                                    <i class="fas fa-building text-indigo-300 text-3xl"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 p-5">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    {{-- Rank Badge --}}
                                    @if($index === 0)
                                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-semibold mb-1 inline-block">
                                            <i class="fas fa-trophy mr-1"></i> Best Match
                                        </span>
                                    @endif
                                    <h3 class="font-semibold text-gray-800">{{ $property->title }}</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $property->location ?? 'Location not set' }}
                                    </p>
                                </div>
                                {{-- Match Score Badge --}}
                                <div class="text-center flex-shrink-0">
                                    <div class="w-14 h-14 rounded-full border-4 {{ $score >= 85 ? 'border-green-400' : ($score >= 70 ? 'border-blue-400' : ($score >= 55 ? 'border-yellow-400' : 'border-orange-400')) }} flex items-center justify-center">
                                        <span class="text-sm font-bold text-gray-800">{{ $score }}%</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 w-16 text-center leading-tight">{{ $property->match_label }}</p>
                                </div>
                            </div>

                            {{-- Property Details --}}
                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                @if($property->bedrooms)
                                    <span><i class="fas fa-bed mr-1"></i>{{ $property->bedrooms }} bed</span>
                                @endif
                                @if($property->bathrooms)
                                    <span><i class="fas fa-bath mr-1"></i>{{ $property->bathrooms }} bath</span>
                                @endif
                                @if($property->area_sqm)
                                    <span><i class="fas fa-ruler-combined mr-1"></i>{{ $property->area_sqm }} sqm</span>
                                @endif
                                @if($property->propertyType)
                                    <span><i class="fas fa-home mr-1"></i>{{ $property->propertyType->name }}</span>
                                @endif
                            </div>

                            {{-- Score Breakdown --}}
                            <div class="grid grid-cols-5 gap-1 mb-3">
                                @php
                                    $breakdownLabels = ['budget' => 'Budget', 'location' => 'Location', 'type' => 'Type', 'bedrooms' => 'Rooms', 'financing' => 'Financing'];
                                    $breakdownMax    = ['budget' => 35, 'location' => 25, 'type' => 20, 'bedrooms' => 15, 'financing' => 5];
                                @endphp
                                @foreach($breakdownLabels as $key => $label)
                                @php
                                    $pts    = $property->match_breakdown[$key] ?? 0;
                                    $max    = $breakdownMax[$key];
                                    $pct    = $max > 0 ? round(($pts / $max) * 100) : 0;
                                    $dotColor = $pct >= 80 ? 'bg-green-400' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-red-300');
                                @endphp
                                <div class="text-center">
                                    <div class="w-2 h-2 rounded-full {{ $dotColor }} mx-auto mb-0.5"></div>
                                    <p class="text-xs text-gray-400" style="font-size:10px">{{ $label }}</p>
                                    <p class="text-xs font-semibold text-gray-700" style="font-size:10px">{{ $pts }}/{{ $max }}</p>
                                </div>
                                @endforeach
                            </div>

                            {{-- Price + CTA --}}
                            <div class="flex items-center justify-between">
                                <p class="text-lg font-bold text-indigo-600">₱{{ number_format($property->price, 0) }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('home.property', $property) }}"
                                        class="text-xs bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">
                                        View Details
                                    </a>
                                    @auth
                                        @if(auth()->user()->isClient())
                                        <a href="{{ route('reservations.create', ['property_id' => $property->id]) }}"
                                            class="text-xs bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                            Reserve
                                        </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- No results after search --}}
            @elseif(request()->isMethod('post'))
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center text-gray-400">
                <i class="fas fa-search text-4xl mb-3 block text-gray-200"></i>
                <p class="font-medium text-gray-600">No matching properties found</p>
                <p class="text-sm mt-1">Try adjusting your budget or preferences.</p>
            </div>

            {{-- Initial state --}}
            @else
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center text-gray-400">
                <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-magic text-indigo-400 text-3xl"></i>
                </div>
                <p class="font-medium text-gray-600 text-lg">Fill in your preferences</p>
                <p class="text-sm mt-2 max-w-xs mx-auto">Tell us your budget, location, and family size — we'll find the best matching properties for you.</p>

                {{-- How it works --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-10 text-left">
                    @foreach([
                        ['icon' => 'fa-sliders-h', 'color' => 'bg-indigo-50 text-indigo-500', 'title' => '1. Set Preferences', 'desc' => 'Enter your budget, location, house model, financing, and family size.'],
                        ['icon' => 'fa-brain',     'color' => 'bg-purple-50 text-purple-500',  'title' => '2. AI Matching',     'desc' => 'Our system scores every available property against your preferences.'],
                        ['icon' => 'fa-home',      'color' => 'bg-green-50 text-green-500',    'title' => '3. Get Results',     'desc' => 'See your top matches ranked by score with a detailed breakdown.'],
                    ] as $step)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="w-10 h-10 {{ $step['color'] }} rounded-xl flex items-center justify-center mb-3">
                            <i class="fas {{ $step['icon'] }}"></i>
                        </div>
                        <p class="font-semibold text-gray-700 text-sm">{{ $step['title'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $step['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
    </div>
</footer>

</body>
</html>
