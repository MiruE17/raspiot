@extends('layouts.windmill')

@section('title', 'Home')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        Home
        <span class="text-sm font-normal block text-gray-600 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}</span>
    </h2>
@endsection

@section('content')
    <!-- Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        <!-- Schemes Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Your Schemes</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ count($schemes) }}</p>
            </div>
        </div>
        
        <!-- Data Points Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Data Points</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $totalDataPoints }}</p>
            </div>
        </div>
        
        <!-- Last Updated Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Last Data Updated</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                    {{ $lastUpdated ? $lastUpdated->diffForHumans() : 'No data yet' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid gap-6 mb-8">
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">
                Data Points by Month
            </h4>
            <div class="h-64">
                <canvas id="dataByMonthChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Schemes -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300 p-4 bg-white dark:bg-gray-800">
            Your Recent Schemes
        </h4>
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Data Points</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($schemes as $scheme)
                    <tr class="text-gray-700 dark:text-gray-400">
                        <td class="px-4 py-3 text-sm">
                            {{ $scheme->name }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $scheme->description ?? 'No description' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $scheme->data_count }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('scheme.show', $scheme->id) }}" class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="text-gray-700 dark:text-gray-400">
                        <td colspan="4" class="px-4 py-3 text-sm text-center">
                            No schemes found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dataByMonthChart').getContext('2d');
        
        // Extract data from PHP
        const dataByMonth = {!! json_encode($dataByMonth) !!};
        
        // Format data for Chart.js
        const labels = dataByMonth.map(item => {
            const date = new Date(item.month);
            return date.toLocaleDateString('default', { month: 'short', year: 'numeric' });
        });
        
        const dataValues = dataByMonth.map(item => item.count);
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Data Points',
                    backgroundColor: 'rgba(66, 153, 225, 0.2)',
                    borderColor: 'rgba(66, 153, 225, 1)',
                    data: dataValues,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: document.documentElement.classList.contains('theme-dark') ? 'white' : '#666'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('theme-dark') ? '#cbd5e1' : '#666'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('theme-dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            color: document.documentElement.classList.contains('theme-dark') ? '#cbd5e1' : '#666'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('theme-dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });

        // Update chart colors when theme changes
        window.addEventListener('theme-change', function() {
            const isDark = document.documentElement.classList.contains('theme-dark');
            chart.options.plugins.legend.labels.color = isDark ? 'white' : '#666';
            chart.options.scales.x.ticks.color = isDark ? '#cbd5e1' : '#666';
            chart.options.scales.y.ticks.color = isDark ? '#cbd5e1' : '#666';
            chart.options.scales.x.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            chart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            chart.update();
        });
    });
</script>
@endpush