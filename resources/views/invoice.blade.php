@foreach ($results as $result)
<div class="panel panel-default">
    <div class="panel-body">Customer {{ $result['Customer'] }} - {{ $result['Total'] }} {{ $currency }} </div>
</div>
@endforeach