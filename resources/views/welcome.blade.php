<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link href="{{ asset('css/div-table.css') }}" rel="stylesheet" type="text/css" >
        <link href="{{ asset('css/jquery.datetimepicker.min.css') }}" rel="stylesheet" type="text/css" >
        <link href="{{ asset('css/jquery.ui.timepicker.css') }}" rel="stylesheet" type="text/css" >
        <link href="{{ asset('css/main.css') }}" rel="stylesheet" type="text/css" >

        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="/js/jquery.datetimepicker.full.min.js"></script>
        <script src="/js/jquery.ui.timepicker.js"></script>

        <script>
          $( function() {
            $('body').on('focus', ".datepicker", function() {
              $(this).datepicker({
                dateFormat: 'yy-mm-dd'
              });
            });

            $('.add-input-button').click(function(){
              $('.divTableBody').append('<div class="divTableRow"> ' +
                  '<div class="divTableCell">Date: <input type="text" class="datepicker" name="date[]" required></div>' +
                  '<div class="divTableCell">Budget: <input type="number" step="0.1" min="0" name="number[]" required ></div>' +
                  '<div class="divTableCell"><button type="button" class="remove-button">Delete</div>' +
                '</div>');
            });

            $('body').on('focus', '.remove-button', function () {
              $(this).parent().parent().remove();
            });
          } );
        </script>
        <title>AdWordsBudgets</title>
    </head>
    <body>
        <div class="container">
          <div>Input:</div>
          <form action="/generateCosts" method="POST" autocomplete="off">
            <div class="divTable">
              <div class="divTableBody">
                <div class="divTableRow">
                  <div class="divTableCell">Date: <input type="text" class="datepicker" name="date[]" required></div>
                  <div class="divTableCell">Budget: <input type="number" step="0.1" min="0" name="number[]" required></div>
                </div>
              </div>
              <div class="add-input-field">
                <button class="add-input-button" type="button">Add input field</button>
              </div>
            </div>
            <div>
              <button type="submit" class="submit-button">Generate output</button>
            </div>
            @csrf
          </form>
        </div>


        @if(isset($adWordsCosts))
        <div class="output-container">
          <div class="title">Input:</div>
          <div>Budget History:</div><br>
            @foreach ($inputBudget as $date => $budgetInfo)
              <div>
              {{ date('d.m.Y', strtotime($date)) }}:
                @if(isset($budgetInfo['dailyBudget']))
                  {{ implode(",", $budgetInfo['dailyBudget']) }}
                @else
                  0
                @endif
              </div>
            @endforeach
          <br>
          <div class="title">Output:</div>
          <div>1. Costs generated</div><br>
            @foreach ($adWordsCosts as $date => $costsInfo)
              <div>
              {{ $date }}:
                @if(isset($costsInfo['costs']))
                  {{ implode(",", $costsInfo['costs']) }}
                @else
                  0
                @endif
              </div>
            @endforeach
          <br>
          <div>2. Daily History Report</div>
          <div class="divTable2">
            <div class="divTableRow" id="divTableHead">
              <div class="divTableCell">Date</div>
              <div class="divTableCell">Budget</div>
              <div class="divTableCell">Costs</div>
            </div>

            @foreach ($adWordsCosts as $date => $costsInfo)
              <div class="divTableRow">
                <div class="divTableCell">{{ $date }}</div>
                <div class="divTableCell">{{ $costsInfo['budget'] }}</div>
                <div class="divTableCell">{{ $costsInfo['totalCosts'] }}</div>
              </div>
            @endforeach
          </div>
          </div>
      @endif
    </body>
</html>
