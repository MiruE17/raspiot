@extends('layouts.windmill')

@section('title', 'Home')

@section('header')
    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
        Home
    </h2>
@endsection

@section('content')
    <!-- Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-3">
        <!-- Users Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $stats['users'] }}</p>
            </div>
        </div>
        
        <!-- Schemes Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Schemes</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $stats['schemes'] }}</p>
            </div>
        </div>
        
        <!-- Data Points Card -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full dark:text-purple-100 dark:bg-purple-500">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Data Points</p>
                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $stats['data_points'] }}</p>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dataByMonthChart').getContext('2d');
        
        // Extract data from PHP
        const dataByMonth = {!! json_encode($stats['data_by_month']) !!};
        
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
                    backgroundColor: 'rgba(169, 92, 232, 0.2)',
                    borderColor: 'rgba(169, 92, 232, 1)',
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