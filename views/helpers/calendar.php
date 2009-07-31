<?php	
/**
 * Calendar Helper for CakePHP
 *
 * Copyright 2007-2008 John Elliott
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * @author John Elliott
 * @copyright 2008 John Elliott
 * @link http://www.flipflops.org More Information
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package app
 * @subpackage app.views.helpers
 * @version .7
 */
class CalendarHelper extends HtmlHelper {

	var $_monthList = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
	var $_dayList = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
	/**
	* Perpares a list of GET params that are tacked on to next/prev. links.
	* @retunr string - urlencoded GET params.
	*/
	function getParams() {
		$params=array();
		foreach ($this->params['url'] as $key=>$val)
		if ($key != 'url')
		$params[]=urlencode($key).'='.urlencode($val);
		return (count($params)>0?'?'.join('&',$params):'');
	}
	/**
	* Generates a Calendar for the specified by the month and year params and populates it with the content of the data array
	*
	* @param $year string
	* @param $month string
	* @param $data array
	* @param $base_url
	* @return string - HTML code to display calendar in view
	*
	*/
	function calendar($currentMonthsYear, $monthName, $data = NULL, $baseUrl = NULL){
		if(!isset($currentYear)){
			$currentMonthsYear = date('Y');
		}
		if(!isset($monthName) or !array_search(strtolower($monthName), $monthList)){
			$monthName = date('F');
		}
		$str = '';
		$day = 1;
		$today = 0;
		$nextYear = $currentMonthsYear;
		$previousYear = $currentMonthsYear;
		$monthNumber = array_search(strtolower($monthName), $monthList) + 1;
		$nextMonth = $monthNumber + 1;
		$previousMonth = $monthNumber - 1;
		if ($monthNumber == 1){
			$previousMonth = 12;
			$previousYear--;
		}
		if ($monthNumber == 12){
			$nextMonth = 1;
			$nextYear++;
		}
		$previousMonthName = $monthList[$previousMonth-1];
		$nextMonthName = $monthList[$nextMonth-1];
		$previousString = $previousYear."/".ucfirst($previousMonthName);
		$nextString = $nextYear."/".ucfirst($nextMonthName);
		if($currentMonthsYear == date('Y') && $monthNumber == date('m')) {
			// set the flag that shows todays date but only in the current month - not past or future...
			$today = date('j');
		}
		$days_in_month = date("t", mktime(0, 0, 0, $monthNumber, 1, $currentMonthsYear));
		$first_day_in_month = date('D', mktime(0,0,0, $monthNumber, 1, $currentMonthsYear));
		$str .= '<table class="calendar">';
		$str .= '<thead>';
		$str .= '<tr><th class="cell-prev">';
		$str .= parent::link(__('prev', true), $baseUrl.'/' . $previousString);
		$str .= '</th><th colspan="5">' . ucfirst($monthName) . ' ' . $currentMonthsYear . '</th><th class="cell-next">';
		$str .= parent::link(__('next', true), $baseUrl.'/' . $nextString);
		$str .= '</th></tr>';
		$str .= '<tr>';
		for($i = 0; $i < 7;$i++) {
			$str .= '<th class="cell-header">' . $this->_dayList[$i] . '';
		}
		$str .= '</tr>';
		$str .= '</thead>';
		$str .= '<tbody>';
		
		while($day <= $days_in_month) {
			$str .= '<tr>';
			for($i = 0; $i < 7; $i ++) {
				$cell = '&nbsp;';
				$onClick='';
				$class = '';
				$style ='';
				if($i > 4) {
					$class = ' class="cell-weekend" ';
				}
				if($day == $today) {
					$class = ' class="cell-today" ';
				}
				if(isset($data[$day]))
				{
					if (is_array($data[$day]))
					{
						if (isset($data[$day]['onClick']))
						{
							$onClick = ' onClick="'.$data[$day]['onClick'].'"';
							$style= ' style="cursor:pointer;"';
						}
						if (isset($data[$day]['content']))
						$cell = $data[$day]['content'];
						if (isset($data[$day]['class']))
						$class = ' class="'.$data[$day]['class'].'"';
					} else
					$cell = $data[$day];
				}
				if(($first_day_in_month == $this->_dayList[$i] || $day > 1) && ($day <= $days_in_month)) {
					$str .= '<td ' . $class .$style.$onClick.' id="cell-'.$day.'"><div class="month-cell-number">' . $day . '</div><div class="cell-data">' . $cell . '</div>';
					$day++;
				} else {
					$str .= '<td ' . $class . '>&nbsp;</td>';
				}
			}
			$str .= '</tr>';
		}
		$str .= '</tbody>';
		$str .= '</table>';
		return $str;
	}

	/**
	* Generates a Calendar for the week specified by the day, month and year params and populates it with the content of the data array
	*
	* @param $year string
	* @param $month string
	* @param $day string
	* @param $data array[day][hour]
	* @param $base_url
	* @return string - HTML code to display calendar in view
	*
	*/
	function week($year = '', $month = '', $day = '', $data = '', $base_url ='', $min_hour = 8, $max_hour=24) {
		$str = '';
		$today = 0;
		if($year == '' || $month == '') { // just use current yeear & month
			$year = date('Y');
			$month = date('F');
			$day = date('d');
			$month_num = date('m');
		}
		$flag = 0;
		for($i = 0; $i < 12; $i++) {
			if(strtolower($month) == $this->_monthList[$i]) {
				if(intval($year) != 0) {
					$flag = 1;
					$month_num = $i + 1;
					break;
				}
			}
		}
		if ($flag == 1)
		{
			$days_in_month = date("t", mktime(0, 0, 0, $month_num, 1, $year));
			if ($day <= 0 || $day > $days_in_month)
			$flag = 0;
		}
		if($flag == 0) {
			$year = date('Y');
			$month = date('F');
			$month_num = date('m');
			$day = date('d');
			$days_in_month = date("t", mktime(0, 0, 0, $month_num, 1, $year));
		}
		$next_year = $year;
		$prev_year = $year;
		$next_month = intval($month_num);
		$prev_month = intval($month_num);
		$next_week = intval($day) + 7;
		$prev_week = intval($day) - 7;
		if ($next_week > $days_in_month)
		{
			$next_week = $next_week - $days_in_month;
			$next_month++;
		}
		if ($prev_week <= 0)
		{
			$prev_month--;
			$prev_week = date('t', mktime(0,0,0, $prev_month,$year)) + $prev_week;
		}
		$next_month_num = null;
		if($next_month == 13) {
			$next_month_num = 1;
			$next_month = 'january';
			$next_year = intval($year) + 1;
			} else {
			$next_month_num = $next_month;
			$next_month = $this->_monthList[$next_month -1];
		}
		$prev_month_num = null;
		if($prev_month == 0) {
			$prev_month_num = 12;
			$prev_month = 'december';
			$prev_year = intval($year) - 1;
			} else {
			$prev_month_num = $prev_month;
			$prev_month = $this->_monthList[$prev_month - 1];
		}
		if($year == date('Y') && strtolower($month) == strtolower(date('F'))) {
			// set the flag that shows todays date but only in the current month - not past or future...
			$today = date('j');
		}
		//count back day until its monday
		while ( date('D', mktime(0,0,0, $month_num, $day, $year)) != 'Mon')
		$day--;
		$title = '';
		if ($day+6>$days_in_month)
		{
			if ($next_month == 'january')
			$title = ucfirst($month).' '.$year.' / '.ucfirst($next_month).' '. ($year+1);
			else
			$title = ucfirst($month).'/'.ucfirst($next_month).' '.$year;
		} else
		$title = ucfirst($month).' '.$year;
		$str .= '<table class="calendar">';
		$str .= '<thead>';
		$str .= '<tr><th class="cell-prev">';
		$str .= parent::->link(__('prev', true), $base_url.'/' . $prev_year . '/' . $prev_month.'/'.$prev_week.$this->getParams());
		$str .= '</th><th colspan="5">' . $title . '</th><th class="cell-next">';
		$str .= parent::->link(__('next', true), $base_url.'/' . $next_year . '/' . $next_month.'/'.$next_week.$this->getParams());
		$str .= '</th></tr>';
		$str .= '<tr>';
		for($i = 0; $i < 7;$i++) {
			$offset = 0;
			if ($day+$i > $days_in_month)
			$offset = $days_in_month;
			else if ($day+$i < 1)
			$offset = - date('t',mktime(1,1,1,$prev_month_num,1,$prev_year));
			$str .= '<th class="cell-header">' . $this->_dayList[$i] . '<br />'.($day+$i-$offset).'</th>';
		}
		$str .= '</tr>';
		$str .= '</thead>';
		$str .= '<tbody>';
		for($hour=$min_hour;$hour < $max_hour;$hour++) {
			$str .= '<tr>';
			for($i = 0; $i < 7; $i ++) {
				$offset = 0;
				if ($day+$i > $days_in_month)
				$offset = $days_in_month;
				else if ($day+$i < 1)
				$offset = - date('t',mktime(1,1,1,$prev_month_num,1,$prev_year));
				$cell = '';
				$onClick='';
				$style ='';
				$class = '';
				if($i > 4) {
					$class = ' class="cell-weekend" ';
				}
				if(($day+$i) == $today && $month_num == date('m') && $year == date('Y')) {
					$class = ' class="cell-today" ';
				}
				if(isset($data[$day+$i-$offset][$hour])) {
					if (is_array($data[$day+$i-$offset][$hour]))
					{
						if (isset($data[$day+$i-$offset][$hour]['onClick']))
						{
							$onClick = ' onClick="'.$data[$day+$i-$offset][$hour]['onClick'].'"';
							$style= ' style="cursor:pointer;"';
						}
						if (isset($data[$day+$i-$offset][$hour]['content']))
						$cell = $data[$day+$i-$offset][$hour]['content'];
						if (isset($data[$day+$i-$offset][$hour]['class']))
						$class = ' class="'.$data[$day+$i-$offset][$hour]['class'].'"';
					} else
					$cell = $data[$day+$i-$offset][$hour];
				}
				$str .= '<td '.$class.$onClick.$style.' id="cell-'.($day+$i-$offset).'-'.$hour.'"><div class="week-cell-number">' . $hour.':00' . '</div><div class="cell-data">' . $cell . '</div></td>';
			}
			$str .= '</tr>';
		}
		$str .= '</tbody>';
		$str .= '</table>';
		return $str;
	}
}
?>