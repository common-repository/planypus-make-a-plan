<?php
/*
Plugin Name: Planypus Links for Event Calendar 3
Version: 1.0
Plugin URI: http://planyp.us/link
Description: integrates Planypus Links with Event Calender http://wpcal.firetree.net/.  When looking at a specific day on the calendar, events are created for that day.  When looking at any other page, event will be created for the closest day in the future or today as scheduled. 
Author: Planypus
Author URI: http://planyp.us
*/

/*
Change Log

1.0
  * First public release.
*/
$planypus_subplugin_get_date_time = 'ev3';

function planypus_subplugin_get_date_time($post) {
  $date_format=get_option('date_format');
  $time_format=get_option('time_format');
  $now = time();
  global $m;
  if ($m != 0) {  //Looking at specific day
    $year = intval(substr($m, 0, 4)); $month = intval(substr($m, 4, 2)); $day = intval(substr($m, 6, 4));
    
    $m_date = mktime(0,0,0, $month,$day,$year);
    
    if ($post && $post->ec3_schedule) {
      //try to find this day in the schedule to get the time
      foreach($post->ec3_schedule as $s) {
        
        $date_start=mysql2date($date_format,$s->start);
        $time_start=mysql2date($time_format,$s->start);
     
        if ($m_date == ec3_to_time($date_start)) {
          $planypus_time = $time_start;
        }
      }    
    }
    
    return array($year . "-" . $month . "-" . $day, $planypus_time);
  } else if (!$post || !$post->ec3_schedule){ //no schedule
    return array(to_planypus_date($now));
  } else {  //Looking at event on front page, or event details      
        
    $date = 0;
    foreach($post->ec3_schedule as $s) {
      $date_start=mysql2date($date_format,$s->start);
      $date_end  =mysql2date($date_format,$s->end);
      $time_start=mysql2date($time_format,$s->start);
      
      $start = ec3_to_time($date_start);
      $end =  ec3_to_time($date_end);
      if ($start <= $now && $end >= $now) {  //now is in the middle of scheduled period
        return array(to_planypus_date($now));
      } else if ($start > $now && ($date == 0 || $start < $date)) { //pick the next upcoming day
        $date = $start;
        $time = $time_start;
      }
      
    } 
    if ($date != 0) return array(to_planypus_date($date), $time);
  }
  return  array(to_planypus_date($today));
}

function to_planypus_date($unixtime) {
  return ec3_strftime("%Y-%m-%d",$unixtime);
}

?>
