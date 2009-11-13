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
 * @version .9
 */
class CalendarHelper extends Helper {
	var $_monthList = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	var $_dayList = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	var $helpers = array('Html');

/**
 * Prepares a list of GET params that are tacked on to next/prev. links.
 * @return string - urlencoded GET params.
 */
	function getParams() {
		$params = array();
		foreach ($this->params['url'] as $key => $val)
		if ($key != 'url') {
			$params[] = urlencode($key) . '=' . urlencode($val);
		}
		return ((count($params)>0) ? '?' . join('&',$params) : '');
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
	function calendar($currentMonthsYear = null, $monthName = null, $data = null, $baseUrl, $modelName = 'Event'){
		$currentMonthsYear = (isset($currentMonthsYear)) ? $currentMonthsYear : date('Y');
		$monthName = (!isset($monthName) or !in_array($monthName, $this->_monthList)) ? date('F') : $monthName;
		$day = 1;
		$today = 0;
		$monthNumber = array_search($monthName, $this->_monthList) + 1;
		if ($monthNumber == 1){
			$nextMonth = 2;
			$nextYear = $currentMonthsYear;
			$previousMonth = 12;
			$previousYear = $currentMonthsYear - 1;
		} elseif ($monthNumber == 12){
			$nextMonth = 1;
			$nextYear = $currentMonthsYear + 1;
			$previousMonth = 11;
			$previousYear = $currentMonthsYear;
		} else {
			$nextMonth = $monthNumber + 1;
			$nextYear = $currentMonthsYear;
			$previousMonth = $monthNumber - 1;
			$previousYear = $currentMonthsYear;
		}
		$previousMonthName = $this->_monthList[$previousMonth-1];
		$nextMonthName = $this->_monthList[$nextMonth-1];
		$previousString = "{$previousYear}/".ucfirst($previousMonthName);
		$nextString = "{$nextYear}/".ucfirst($nextMonthName);
		if($currentMonthsYear == date('Y') && $monthNumber == date('m')) {
			// set the flag that shows todays date but only in the current month - not past or future...
			$today = date('j');
		}
		$daysInMonth = date("t", mktime(0, 0, 0, $monthNumber, 1, $currentMonthsYear));
		$firstDayInMonth = date('D', mktime(0, 0, 0, $monthNumber, 1, $currentMonthsYear));
		$str = '<table cellspacing="0" class="calendar"><thead><tr><th class="cell-prev nav">';
		$str .= $this->Html->link(__(substr("{$previousMonthName}", 0, 3), true), "{$baseUrl}/{$previousString}");
		$str .= '</th><th class="padding month" colspan="5">' . ucfirst($monthName) . " {$currentMonthsYear}</th><th class='cell-next nav'>";
		$str .= $this->Html->link(__(substr("{$nextMonthName}", 0, 3), true), "{$baseUrl}/{$nextString}") . '</th></tr>';
		$str .= '<tr>';
		for($i = 0; $i < 7;$i++) {
			$str .= "<th class='day_of_week'>{$this->_dayList[$i]}</th>";
		}
		$str .= '</tr></thead><tbody>';
		while($day <= $daysInMonth) {
			$str .= '<tr class="calendar-cells">';
			for($i = 0; $i < 7; $i ++) {
				$class = ($day == $today) ? ' today' : '';
				if (($firstDayInMonth == $this->_dayList[$i] || $day > 1) && ($day <= $daysInMonth)) {
					if (in_array($day, array_keys($data)) and strlen($day) != 6) {
						if (isset($data[$day])) {
							$dayStuff = '';
							foreach($data[$day] as $aDay) {
								if (!empty($aDay)) {
									$eventLink = $this->Html->link($aDay[$modelName]['title'], array('controller' => 'events', 'action' => 'view', $aDay[$modelName]['id'], $aDay[$modelName]['slug']), array('class' => 'eventTitle'));
									if (isset($aDay[$modelName]['url']) and !empty($aDay[$modelName]['url'])) {
										$link = $this->Html->link('external link', $aDay[$modelName]['url'], array('class' => 'eventLink'));
										$dayStuff .= "<li><span class='eventTitle'>{$eventLink}</span> <span class='eventLink'>({$link})</span></li>";
									} else {
										$dayStuff .= "<li><span class='eventTitle'>{$$eventLink}</span></li>";
									}
								}
							}
							$numberOfEvents = count($data[$day]);
							$todaysEventCount = '';
							if ($numberOfEvents > 1) {
								$todaysEventCount = "{$numberOfEvents} Events";
							} else {
								$todaysEventCount = "{$numberOfEvents} Event";
							}
							$dayLink = $this->Html->link($day, array('controller' => 'events', 'action' => 'date', $currentMonthsYear, $monthName, $day), array('class' => 'eventDateLink'));
							$str .= "<td class='date_has_event{$class}'><div class='day_header'><span class='event'>{$todaysEventCount}</span></div><div class='day'>{$dayLink}</div><div class='events'><ul>{$dayStuff}</ul></div></td>";
						}
					} elseif (empty($class)) {
						$str .= "<td><div class='day_header'></div><div class='day'>{$day}</div></td>";
					} else {
						$str .= "<td class='today'><div class='day_header'></div><div class='day'>{$day}</div></td>";
					}
					$day++;
				} else {
					$str .= '<td class="padding" colspan="1">&nbsp;</td>';
				}
			}
			$str .= '</tr>';
		}
		$str .= '</tbody></table>';
		return $str;
	}

	function jquery() {
		echo $this->Html->scriptBlock("$(function () {
			$('.date_has_event').each(function () {
				// options
				var distance = 10;
				var time = 350;
				var hideDelay = 500;

				var hideDelayTimer = null;

				// tracker
				var beingShown = false;
				var shown = false;

				var trigger = $(this);
				var popup = $('.events ul', this).css('opacity', 0);

				// set the mouseover and mouseout on both element
				$([trigger.get(0), popup.get(0)]).mouseover(function () {
					// stops the hide event if we move from the trigger to the popup element
					if (hideDelayTimer) clearTimeout(hideDelayTimer);

					// don't trigger the animation again if we're being shown, or already visible
					if (beingShown || shown) {
						return;
					} else {
						beingShown = true;

						// reset position of popup box
						popup.css({
							bottom: 20,
							left: -76,
							display: 'block' // brings the popup back in to view
						})

						// (we're using chaining on the popup) now animate it's opacity and position
						.animate({
							bottom: '+=' + distance + 'px',
							opacity: 1
						}, time, 'swing', function() {
							// once the animation is complete, set the tracker variables
							beingShown = false;
							shown = true;
						});
					}
				}).mouseout(function () {
					// reset the timer if we get fired again - avoids double animations
					if (hideDelayTimer) clearTimeout(hideDelayTimer);

					// store the timer so that it can be cleared in the mouseover if required
					hideDelayTimer = setTimeout(function () {
						hideDelayTimer = null;
						popup.animate({
							bottom: '-=' + distance + 'px',
							opacity: 0
						}, time, 'swing', function () {
							// once the animate is complete, set the tracker variables
							shown = false;
							// hide the popup entirely after the effect (opacity alone doesn't do the job)
							popup.css('display', 'none');
						});
					}, hideDelay);
				});
			});
		});", array('safe' => false));
	}
}
?>