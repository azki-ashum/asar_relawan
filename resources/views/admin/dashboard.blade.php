@extends('layouts.app')

@section('title', 'Admin Dashboard - Statistik')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Admin Dashboard</h3>
            <div>
                <form method="get" class="d-flex align-items-center">
                    <label class="me-2 small text-muted">Range</label>
                    <select name="range" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="day" {{ $range === 'day' ? 'selected' : '' }}>Hari</option>
                        <option value="week" {{ $range === 'week' ? 'selected' : '' }}>Minggu</option>
                        <option value="month" {{ $range === 'month' ? 'selected' : '' }}>Bulan</option>
                        <option value="year" {{ $range === 'year' ? 'selected' : '' }}>Tahun</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mb-4">
            {{-- Card 1 --}}
            <div class="flex-fill" style="min-width:220px;">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    <div class="small text-muted">Total Booking</div>
                    <div class="h4 fw-bold">{{ number_format($totalBookings) }}</div>
                    <div class="card-meta text-muted small mt-auto">Periode: {{ $start->format('Y-m-d') }} — {{ $end->format('Y-m-d') }}</div>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="flex-fill" style="min-width:220px;">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    <div class="small text-muted">Booking Ruangan</div>
                    <div class="h4 fw-bold">{{ number_format($roomBookings) }}</div>
                    <div class="card-meta text-muted small mt-auto">Jumlah Ruangan: {{ $roomsCount }}</div>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="flex-fill" style="min-width:220px;">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    <div class="small text-muted">Booking Kendaraan</div>
                    <div class="h4 fw-bold">{{ number_format($assetBookings) }}</div>
                    <div class="card-meta text-muted small mt-auto">Jumlah Kendaraan: {{ $assetsCount }}</div>
                </div>
            </div>

            {{-- Card 4 --}}
            <div class="flex-fill" style="min-width:220px;">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    <div class="small text-muted">Top Pemesan Ruangan</div>
                    <p class="fw-bold mt-2" style="font-size: 0.9rem">{{ $topUsersRoom->first()['name'] ?? ($topUsers->first()['name'] ?? '-') }}</p>
                    <div class="card-meta text-muted small mt-auto">Jumlah: {{ $topUsersRoom->first()['total'] ?? ($topUsers->first()['room_count'] ?? 0) }}</div>
                </div>
            </div>

            {{-- Card 5 --}}
            <div class="flex-fill" style="min-width:220px;">
                <div class="card h-100 shadow-sm p-3 d-flex flex-column">
                    <div class="small text-muted">Top Pemesan Kendaraan</div>
                    <p class="fw-bold mt-2" style="font-size: 0.9rem">{{ $topUsersAsset->first()['name'] ?? ($topUsers->first()['name'] ?? '-') }}</p>
                    <div class="card-meta text-muted small mt-auto">Jumlah: {{ $topUsersAsset->first()['total'] ?? ($topUsers->first()['asset_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>Booking Over Time</div>
                        <div>
                            <form method="get" class="d-flex align-items-center">
                                <input type="hidden" name="range" value="{{ $range }}">
                                <label class="me-2 small text-muted mb-0">Type</label>
                                <select name="type" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="room" {{ ($type ?? '') === 'room' ? 'selected' : '' }}>Ruangan</option>
                                    <option value="asset" {{ ($type ?? '') === 'asset' ? 'selected' : '' }}>Kendaraan</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartBookings" height="120"></canvas>
                    </div>
                </div>

                <div class="card mt-3 shadow-sm">
                    <div class="card-header">Top Users</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th class="text-end">Ruangan</th>
                                    <th class="text-end">Kendaraan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsers as $u)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $u['name'] }}</div>
                                            <div class="small text-muted">{{ $u['email'] }}</div>
                                        </td>
                                        <td class="text-end">{{ $u['room_count'] ?? 0 }}</td>
                                        <td class="text-end">{{ $u['asset_count'] ?? 0 }}</td>
                                        <td class="text-end">{{ $u['total'] ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">Top Kendaraan</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Kendaraan</th><th class="text-end">Jumlah</th></tr>
                            </thead>
                            <tbody>
                                @foreach($topAssets as $a)
                                    <tr>
                                        <td>{{ $a['name'] }}</td>
                                        <td class="text-end">{{ $a['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card mt-3 shadow-sm">
                    <div class="card-header">Top Ruangan</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Ruangan</th><th class="text-end">Jumlah</th></tr>
                            </thead>
                            <tbody>
                                @foreach($topRooms as $r)
                                    <tr>
                                        <td>{{ $r['name'] }}</td>
                                        <td class="text-end">{{ $r['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function(){
            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartSeries) !!};

            const ctx = document.getElementById('chartBookings').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.08)',
                        tension: 0.2,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })();
    </script>
@endpush
