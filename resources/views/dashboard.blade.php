@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if($country_name!='Worldwide')
                <a href="/" class="btn btn-secondary float-right">Go Back</a>
            @endif
            <h2>Dashboard</h2>
            <div id="container"></div>
            <div class="pt-4">
                <h3>Statistics</h3>
                <div id="statistics"></div>
                <p class="pt-4">Total Confirmed Cases: {{$dtcases->last()->count}}</p>
                <p>Percentage of population affected: {{number_format($dtcases->last()->count * 100.00 / $pcount, 2, '.', '')}}%</p>
                <table class="table table-sm col-md-4">
                    <thead>
                      <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Cases</th>
                        <th scope="col">Change</th>
                      </tr>
                    </thead>
                    <tbody>
                        @for($i=0; $i<5; $i++)    
                        <tr>
                            <td>{{$rdtcases[$i]->created_at->toFormattedDateString()}}</td>
                            <td>{{$rdtcases[$i]->count}}</td>
                            @if($rdtcases[$i+1]->count != 0)
                            <td>{{round(($rdtcases[$i]->count-$rdtcases[$i+1]->count)*100 / $rdtcases[$i+1]->count, 2)}}%</td>
                            @else
                            <td>inf</td>
                            @endif
                        </tr>
                        @endfor
                    </tbody>
                  </table>
            </div>
            <div class="pt-4">
                <h3>Relevant News</h3>
                <div class="row row-cols-1 row-cols-md-4">
                    @foreach($news['articles'] as $n)
                        <div class="col mb-4">
                            <div class="card h-60">
                            <a href="{{$n['url']}}"><img src="{{$n['urlToImage']}}" class="card-img-top"></a>
                            <div class="card-body">
                                <a href="{{$n['url']}}"><h5 class="card-title">{{$n['title']}}</h5></a>
                                <p class="card-text">{{$n['description']}}</p>
                            </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
    var data = [
        @foreach($cases as $p)
        {"code": "{{strtoupper($p->ccode())}}", "value": {{$p->count}}, "id": "{{strtoupper($p->ccode())}}" },
        @endforeach
    ];

    Highcharts.mapChart('container', {
        chart: {
            map: 'custom/world',
            height: 600,
            @if($country_name!='Worldwide')
            events: {
            load: function () {
                    this.get('{{strtoupper($p->ccode())}}').zoomTo();
                }
            }
            @endif
        },

        title: {
            text: '{{$country_name}} Covid-19 Cases'
        },

        mapNavigation: {
            enabled: true,
            buttonOptions: {
                verticalAlign: 'bottom'
            }
        },

        colorAxis: {
            minColor: '#efecf3',
            maxColor: '#cc3b3b',
            min: 1,
        },
        
        legend: {
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'middle'
        },

        series: [{
            data: data,
            name: 'Cases',
            joinBy: ['iso-a2', 'code'],
            states: {
                hover: {
                    color: Highcharts.getOptions().colors[2]
                }
            },
            dataLabels: {
                enabled: true,
                format: '{point.name}'
            },
            events: {
                click: function(e){
                    window.location = '/' + e.point.code.toLowerCase();
                }
            }
        }]
    });

    Highcharts.chart('statistics', {
        chart: {
            type: 'spline',
            zoomType: 'x'
        },
        title: {
            text: '{{$country_name}} Cases per day'
        },
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: {
            month: '%e. %b',
            year: '%b'
            },
            title: {
            text: 'Date'
            }
        },
        yAxis: [{
            title: {
            text: 'Total Cases'
            },
            min: 0
        },
        {
            title: {
            text: ' Daily Cases'
            },
            min: 0
        }],
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y} cases'
        },

        plotOptions: {
            series: {
            marker: {
                enabled: false
            }
            }
        },

        colors: ['#0EB1D2', '#457B9D'],
        series: [{
            name: "Total Cases",
            yAxis: 0,
            data: [
            @foreach($dtcases as $dt)
                [Date.UTC({{$dt->created_at->year}}, {{$dt->created_at->month - 1}}, {{$dt->created_at->day}}), {{$dt->count}}],
            @endforeach
            ]
        },
        {
            name: "Daily Cases",
            yAxis: 1,
            data: [
            @foreach($dcases as $dt)
                [Date.UTC({{$dt['created_at']->year}}, {{$dt['created_at']->month - 1}}, {{$dt['created_at']->day}}), {{$dt['count']}}],
            @endforeach
            ]
        }
        ],

        responsive: {
            rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                plotOptions: {
                series: {
                    marker: {
                    radius: 2.5
                    }
                }
                }
            }
            }]
        }
        });
</script>
@endsection