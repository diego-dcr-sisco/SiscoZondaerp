@php
    use Carbon\Carbon;
    $date = Carbon::now();
    $count = 0;
@endphp

@foreach ($charts as $name => $chart)
    <div class="col-6 mb-3">
        <div class="card shadow-sm border-dark">
            <div class="card-body">
                <h5 class="card-title fw-bold d-flex justify-content-between">
                    <span> {{ $chartNames[$count] }} </span>
                    <select class="form-select  w-25"
                        onchange="updateChart{{$name}}(this.value, '{{ $name }}')">
                        @foreach ($months as $i => $month)
                            <option value="{{ $i + 1 }}" {{ $date->month == $i + 1 ? 'selected' : '' }}>
                                {{ $month }} </option>
                        @endforeach
                    </select>
                </h5>
                <div id="chart-{{ $name }}">
                    {!! $chart->container() !!}
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/vue"></script>
    <script>
        
    </script>
    {!! $chart->script() !!}

    @php
        $count++;
    @endphp
@endforeach

<script src=https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js charset=utf-8></script>
