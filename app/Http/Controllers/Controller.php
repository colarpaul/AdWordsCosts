<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

use DateTime;
use DatePeriod;
use DateInterval;

class Controller extends BaseController
{

    public function index(Request $request) {

        $dateInput    = $request->input('date');
        $budgetInput  = $request->input('number');

        $budgetReport      = array();
        $dailyBudgetReport = array();

        foreach($dateInput as $key => $date) {

            $budgetReport[$date]['dailyBudget'][]  = $budgetInput[$key];
            $budgetReport[$date]['maxDailyBudget'] = max($budgetReport[$date]['dailyBudget']);
        }

        ksort($budgetReport);

        $dailyBudgetReport = $this->getDailyBudgetReport($budgetReport);

        $dailyBudgetReport['maxMonthlyBudget'] = $this->calculateMonthlyBudget($dailyBudgetReport);

        $adWordsCosts = $this->generateAdWordsCosts($dailyBudgetReport);

        return view('welcome', ['adWordsCosts' => $adWordsCosts, 'inputBudget' => $budgetReport]);
    }

    public function calculateMonthlyBudget($budgetReport) {

      foreach($budgetReport as $day => $data) {

        $month          = $this->getMonth($day);
        $maxDailyBudget = $data['budget'];

        // Set monthly budget to budgetReport array
        if (!isset($budgetReport['maxMonthlyBudget'][$month])){

            $budgetReport['maxMonthlyBudget'][$month] = $maxDailyBudget;
        } else{

            $budgetReport['maxMonthlyBudget'][$month] += $maxDailyBudget;
        }
      }

      if( count($budgetReport['maxMonthlyBudget'] ) > 3) {

          // Validation required
          die('You have more than 3 months selected!');
      }

      return $budgetReport['maxMonthlyBudget'];
    }

    public function getDailyBudgetReport($budgetReport) {

        if(count($budgetReport) > 1) {

          // Set current day (first day)
          reset($budgetReport);
          $currentDate = key($budgetReport);

          // Set last day
          end($budgetReport);
          $lastDate = key($budgetReport);

          // Set next day after first day
          reset($budgetReport);
          next($budgetReport);
          $nextDate = key($budgetReport);

        } else {

          // Only one day in array
          reset($budgetReport);
          $currentDate = $nextDate = $lastDate = key($budgetReport);
        }

        // Get daily budget report from the first day to the last day
        while($nextDate != $lastDate){

            $dailyBudgetReport[] = $this->getBudgetInfoFromStartDateToEndDate($currentDate, $nextDate, $budgetReport[$currentDate]['maxDailyBudget']);
            $currentDate = $nextDate;

            next($budgetReport);
            $nextDate = key($budgetReport);
        }

        $dailyBudgetReport[] = $this->getBudgetInfoFromStartDateToEndDate($currentDate, $nextDate, $budgetReport[$currentDate]['maxDailyBudget']);

        // Get daily budget report for the last day
        $dailyBudgetReport[] = $this->getBudgetInfoFromStartDateToEndDate($nextDate, $lastDate, $budgetReport[$lastDate]['maxDailyBudget']);

        $dailyBudgetReport = call_user_func_array('array_merge', $dailyBudgetReport);

        return $dailyBudgetReport;
    }

    public function generateAdWordsCosts($dailyBudgetReport) {

      $maxMonthlyBudget = $dailyBudgetReport['maxMonthlyBudget'];
      unset($dailyBudgetReport['maxMonthlyBudget']);

      foreach($dailyBudgetReport as $date => $dataInfo) {

        $month = $this->getMonth($date);

        $maxDailyBudget = $dataInfo['maxDailyBudget'];

        $counterCosts = 0;
        $dailyCosts   = 0;

        while( $counterCosts < rand(0, 10) &&
               $dailyCosts < $maxDailyBudget &&
               $dailyCosts < $maxMonthlyBudget[$month] ) {

            $cost = rand(0, 100) / 10;

            if( $cost <= $maxDailyBudget &&
                $cost <= $maxMonthlyBudget[$month] ){

                $maxMonthlyBudget[$month] -= $cost;

                if( $cost != 0 ){

                  $dailyBudgetReport[$date]['costs'][] = $cost;
                }

                $dailyCosts += $cost;
            }

            $counterCosts++;
        }

        if(isset($dailyBudgetReport[$date]['costs'])){

          $dailyBudgetReport[$date]['totalCosts'] = array_sum($dailyBudgetReport[$date]['costs']);
        } else {

          $dailyBudgetReport[$date]['totalCosts'] = 0;
        }
      }

      return $dailyBudgetReport;
    }

    public function getMonth($date) {

      return intval(date("m", strtotime($date)));
    }

    public function getBudgetInfoFromStartDateToEndDate($start, $end, $maxDailyBudget, $format = 'd.m.Y') {

        $budgetInfo = [];

        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach($period as $date) {
            $budgetInfo[$date->format($format)]['budget'] = $maxDailyBudget;
            $budgetInfo[$date->format($format)]['maxDailyBudget'] = $maxDailyBudget * 2;
        }

        return $budgetInfo;
    }
}
