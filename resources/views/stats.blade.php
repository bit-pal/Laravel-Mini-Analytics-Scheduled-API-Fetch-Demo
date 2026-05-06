<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Visit statistics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <div class="text-sm text-gray-600">Site</div>
                    <div class="font-semibold">{{ $site->name }}</div>
                    <div class="text-sm text-gray-600">
                        Embed:
                        <code class="px-2 py-1 bg-gray-100 rounded text-xs">&lt;script src="{{ url('/tracker.js') }}?siteKey={{ $site->site_key }}"&gt;&lt;/script&gt;</code>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="font-semibold mb-4">Unique visits by hour (last 24h)</div>
                        <div class="relative h-72">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="font-semibold mb-4">Unique visits by city (last 24h)</div>
                        <div class="relative h-72">
                            <canvas id="cityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
      (function () {
        'use strict';

        async function getJson(url) {
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error('Request failed: ' + res.status);
          return await res.json();
        }

        function makeHourly(ctx, labels, values) {
          return new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Unique visits',
                data: values,
                backgroundColor: 'rgba(59, 130, 246, 0.35)',
                borderColor: 'rgba(59, 130, 246, 0.9)',
                borderWidth: 1,
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
          });
        }

        function makePie(ctx, labels, values) {
          const colors = [
            '#2563eb','#16a34a','#f97316','#dc2626','#7c3aed','#0ea5e9',
            '#84cc16','#e11d48','#a16207','#0891b2','#4b5563','#be123c'
          ];

          return new Chart(ctx, {
            type: 'pie',
            data: {
              labels: labels,
              datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
              }]
            },
            options: { responsive: true, maintainAspectRatio: false }
          });
        }

        Promise.all([
          getJson('{{ route('stats.hourly') }}'),
          getJson('{{ route('stats.cities') }}'),
        ]).then(([hourly, cities]) => {
          makeHourly(document.getElementById('hourlyChart'), hourly.labels, hourly.values);
          makePie(document.getElementById('cityChart'), cities.labels, cities.values);
        }).catch((e) => {
          console.error(e);
        });
      })();
    </script>
</x-app-layout>

