@extends('layouts.app')

@section('title', 'Generate Prediction - EstateFlow')
@section('page-title', 'Generate Prediction')
@section('page-subtitle', 'Run AI analysis on a property or project')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('ai-predictions.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prediction Type <span class="text-red-500">*</span></label>
                    <select name="prediction_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="price_prediction" {{ old('prediction_type') === 'price_prediction' ? 'selected' : '' }}>Price Prediction</option>
                        <option value="market_analysis"  {{ old('prediction_type') === 'market_analysis'  ? 'selected' : '' }}>Market Analysis</option>
                        <option value="progress_analysis"{{ old('prediction_type') === 'progress_analysis'? 'selected' : '' }}>Progress Analysis</option>
                        <option value="recommendation"   {{ old('prediction_type') === 'recommendation'   ? 'selected' : '' }}>Recommendation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <select name="predictable_type" id="predictable_type" onchange="updateSubjectOptions()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="property">Property</option>
                        <option value="project">Project</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Record <span class="text-red-500">*</span></label>

                    <select name="predictable_id" id="property_select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}">{{ $p->title }} — ₱{{ number_format($p->price, 0) }}</option>
                        @endforeach
                    </select>

                    <select name="predictable_id" id="project_select" class="hidden w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Version</label>
                    <input type="text" name="model_version" value="{{ old('model_version', 'v1.0') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Info Box --}}
            <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                <p class="text-sm text-indigo-700 font-medium mb-1"><i class="fas fa-info-circle mr-1"></i>How it works</p>
                <ul class="text-xs text-indigo-600 space-y-1 list-disc list-inside">
                    <li>Price Prediction — estimates future property value based on area, bedrooms, and market trends</li>
                    <li>Market Analysis — compares property against similar listings in the same category</li>
                    <li>Progress Analysis — evaluates project budget vs completion percentage</li>
                </ul>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-magic mr-1"></i> Generate
                </button>
                <a href="{{ route('ai-predictions.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateSubjectOptions() {
    const type = document.getElementById('predictable_type').value;
    const propertySelect = document.getElementById('property_select');
    const projectSelect  = document.getElementById('project_select');

    if (type === 'property') {
        propertySelect.classList.remove('hidden'); propertySelect.disabled = false;
        projectSelect.classList.add('hidden');     projectSelect.disabled = true;
    } else {
        projectSelect.classList.remove('hidden'); projectSelect.disabled = false;
        propertySelect.classList.add('hidden');   propertySelect.disabled = true;
    }
}
updateSubjectOptions();
</script>
@endsection
