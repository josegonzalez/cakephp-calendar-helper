# CakePHP Calendar Helper
YMMV I am not responsible for anything bad happening with this plugin. No support is given either, at least not yet. Use at your own risk!

This is a quick how-to on usage of the helper. The following is in my Event model

		function getEvents($params = array()) {
			$events = $this->find('all', array(
					'conditions' => array(
						"YEAR({$this->alias}.date)" => $params['year'],
						"MONTHNAME({$this->alias}.date)" => $params['month']),
					'fields' => array('id', 'slug', 'title', 'DAY(date) AS day', 'url'),
					'order' => "{$this->alias}.date ASC"));
			/**
			* loop through the returned data and build an array of 'events' that is passes to the view
			* array key is the day of month
			*/
			$data = array();
			foreach($events as $key => &$event) {
				$event[$this->alias]['day'] = $event['0']['day'];
				unset($event['0']);
				if (!isset($data[$event[$this->alias]['day']])) {
					$data[$event[$this->alias]['day']] = array();
				}
				$data[$event[$this->alias]['day']][] = $event;
			}
			return $data;
		}
	
		function getDateParameters($year = null, $month = null, $day = null) {
			if(is_null($year) or is_null($month) or is_null($day)) {
				$year = date('Y');
				$month = date('F');
				$day = date('d');
			} else {
				$monthList = $this->monthList;
				for($i = 0; $i < 12; $i++) { // check the month is valid if set
					if ((ucfirst(strtolower($month)) == $monthList[$i]) and (intval($year) != 0)) {
						$flag = 1;
						$month = $monthList[$i];
						break;
					}
				}
			}
			return array('year' => $year, 'month' => $month, 'day' => $day);
		}

My Controller:

		function calendar($year = null, $month = null) {
			if (!empty($this->data)) {
				$dataMonth = (isset($this->data['Event']['month'])) ? intval($this->data['Event']['month']['month']) - 1 : date('F');
				$dataMonth = $this->Event->monthList[$dataMonth];
				$dataYear = (isset($this->data['Event']['year'])) ? $this->data['Event']['year']['year'] : date('Y');
				$this->redirect(array('controller' => 'events', 'action' => 'calendar', "{$dataYear}/{$dataMonth}"));
			}
			$baseUrl = Router::url(array('controller' => 'events', 'action' => 'calendar'), true);
			$dateParameters = $this->Event->getDateParameters($year, $month);
			$year = $dateParameters['year'];
			$month = $dateParameters['month'];
			$events = $this->Event->find('calendar', array('year' => $year, 'month' => $month));
			$this->set(compact('year', 'month', 'baseUrl', 'events'));
		}

My view:

		<div class="events-calendar index">
			<h2 class="title">Calendar of Events</h2>
			<?php echo $calendar->calendar($year, $month, $events, $baseUrl); ?>
		</div>
		
		// Put the following in your head!
		echo $calendar->jquery();

Some very simple css:

	/* Calendar */
	#calendar_wrap{padding:10px 15px;text-align:center;}
		#calendar_wrap table{width:100%;}
			#calendar_wrap th{}
			#calendar_wrap td{}
				#calendar_wrap .day_header{display:none;}
				#calendar_wrap td.date_has_event .day{background-color:#555;color:#fff;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;-border-radius:3px;}
					#calendar_wrap .events{position:relative;}
						#calendar_wrap .events ul{background:#fff;border:1px solid white;color:white;display:none;font-size:12px;padding:15px;position:absolute;text-align:right;z-index:1000;width:200px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;-border-radius:3px;list-style:none;color:#444444;-webkit-box-shadow:0px 8px 8px #333;}
						#calendar_wrap .events li{padding-bottom:5px;display:block;}
							#calendar_wrap .events li a{color:#555;text-align:justify;}
								#calendar_wrap .events li a.title{color:#222;font-weight:bold;}
			#calendar_wrap tfoot td{border:none;}
				#calendar_wrap tfoot td#prev{text-align:left;font-weight:bold;border:none;}
					#calendar_wrap tfoot td#prev a{border:none;}
				#calendar_wrap tfoot td#next{text-align:right;font-weight:bold;border:none;}
					#calendar_wrap tfoot td#next a{border:none;}

You need the following fields in your model:

	'id' => Primary Key
	'slug' => String Slug of your Evemt
	'title' => String Event Title
	'DAY(date) AS day' => Date/Datetime for the Event
	'url' => (Optional) a full html url to any external links
