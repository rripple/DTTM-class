<?php

class DTTM
{
	const cSecondsMinute = 60;
	const cMinutesHour   = 60;
	const cSecondsHour   = 3600;
	const cHoursDay      = 24;
	const cMinutesDay    = 1440;
	const cSecondsDay    = 86400;
	const cMonthsYear    = 12;
	const cDaysWeek      = 7;
	const cDaysYear      = 365;
	const cDays4Years    = 1461;
	const cDays100Years  = 36524;
	const cDays400Years  = 146097;
	const cDaysTo1970    = 719162;

	/** @staticvar array $_nDaysMonths 1 based array to hold days in each month */
	static private $_nDaysMonth     = array( 1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

	/** @staticvar array $_szShortDays Abbreviated names of days of the week */
	static private $_szShortDays    = array();

	/** @staticvar array $_szLongDays Full names of Days of the week */
	static private $_szLongDays     = array();

	/** @staticvar array $_szShortMonths Abbr. names of the months */
	static private $_szShortMonths  = array();

	/** @staticvar array $_szLongMonths List of full names of the months. */
	static private $_szLongMonths   = array();

	/** @staticvar array $_szTimeZone List of offsets to timezone names */
	static private $_szTimeZone     = array();

	/** @staticvar integer $TZOFS Minutes offset from GMT */
	static public  $TZOFS           = false;

	/** @staticvar boolean $ISDST If is Daylight Savings or Not on client computer */
	static public  $ISDST           = false;

	/** @staticvar string $FMT_DTTM format of datetime */
	static public  $FMT_DTTM		= 'Y-m-d H:i:s';

	/** @var integer $tm_year Holds the year number of the date */
	private $tm_year    = 0;

	/** @var integer $tm_month Holds the month number of the date */
	private $tm_month   = 0;

	/** @var integer $tm_day Holds the day number of the date */
	private $tm_day     = 0;

	/** @var integer $tm_hour Holds the hour number of the date */
	private $tm_hour    = 0;

	/** @var integer $tm_minute Holds the minute number of the date */
	private $tm_minute  = 0;

	/** @var integer $tm_second Holds the second number of the date */
	private $tm_second  = 0;

	/** @var integer $tm_tzofs Holds the timezone offset number of the date */
	private $tm_tzofs   = 0;

	/** @var integer $tm_dst Holds the daylight savings boolean of the date */
	private $tm_dst     = 0;

	/**
	 * The constructor function to spin up a new DTTM object.
	 *
	 * @param mixed $datetime Can be false for 'now', a string, an array, a dttm obj.
	 * @param boolean $isGTM
	 * @return self|null
	 */
	function __construct( $datetime = false, $isGMT = false, $tzofs = false )
	{
		# SET CONSTANTS
		if( !self::$_szTimeZone )
		{
			self::$_szTimeZone =
				[
					0           => array( 'gmt' ),  # Greenwich Mean
					-1*3600     => array( 'wat' ),  # West Africa
					-2*3600     => array( 'at' ),   # Azores
					-4*3600     => array( 'ast', 'adt' ),   # Atlantic Standard
					-5*3600     => array( 'est', 'edt' ),   # Eastern Standard
					-6*3600     => array( 'cst', 'cdt' ),   # Central Standard
					-7*3600     => array( 'mst', 'mdt' ),   # Mountain Standard
					-8*3600     => array( 'pst', 'pdt' ),   # Pacific Standard
					-9*3600     => array( 'yst', 'ydt' ),   # Yukon Standard
					-10*3600    => array( 'hst', 'hdt' ),   # Hawaii Standard
					-11*3600    => array( 'nt' ),   # Nome
					-12*3600    => array( 'idlw' ), # International Date Line West
					+1*3600     => array( 'cet', 'cedt' ),  # Central European
					+2*3600     => array( 'eet', 'eedt' ),  # Eastern Europe, USSR Zone 1
					+3*3600     => array( 'bt' ),   # Baghdad, USSR Zone 2
					+4*3600     => array( 'zp4' ),  # USSR Zone 3
					+5*3600     => array( 'zp5' ),  # USSR Zone 4
					+5*3600+1800 => array( 'ist' ), # Indian Standard
					+6*3600     => array( 'zp6' ),  # USSR Zone 5
					+6*3600+1800 => array( 'nst' ), #North Sumatra
					+7*3600     => array( 'wast', 'wadt' ), # West Australian Standard
					+8*3600     => array( 'cct' ),  # China Coast, USSR Zone 7
					+9*3600     => array( 'jst' ),  # Japan Standard, USSR Zone 8
					+10*3600    => array( 'east', 'eadt' ), # Eastern Australian Standard
					+12*3600    => array( 'nzt', 'ndzt' ),  # New Zealand
				];

			// get system strings for months and days
			$da = 1;

			// find last day of month .. crude .. but safe
			while( date( "w", mktime( 0, 0, 0, 1, $da, 2000 ) ) != 0 )
				$da++;

			for( $i = 0; $i < 7; $i++ )
			{
				self::$_szShortDays[$i] = strftime( "%a", mktime( 0, 0, 0, 1, $da + $i, 2000 ) );
				self::$_szLongDays[$i] = strftime( "%A", mktime( 0, 0, 0, 1, $da + $i, 2000 ) );
			}

			for( $mo = 1; $mo <= 12; $mo++ )
			{
				self::$_szShortMonths[$mo-1] = strftime( "%b", mktime( 0, 0, 0, $mo, 1, 2000 ) );
				self::$_szLongMonths[$mo-1] = strftime( "%B", mktime( 0, 0, 0, $mo, 1, 2000 ) );
			}
		}

		if( self::$TZOFS === false )
		{
			self::$TZOFS = intval( date( 'Z', time() ) );
		}

		$this->SetDate( $datetime, $isGMT, $tzofs );
	}

	/**
	 * Duration takes two objects and returns either a string or array
	 *
	 * @param object $obj1
	 * @param object $obj2
	 * @param boolean $bWantString
	 * @return string|array|false
	 */
	static function Duration( $obj1, $obj2, $bWantString = false )
	{
		$data = array();

		$obj1 = new DTTM( $obj1, false );
		$obj2 = new DTTM( $obj2, false );

		$diff = abs( $obj1->GetUnixTime() - $obj2->GetUnixTime() );

		if( $diff )
		{
			$data[] = (int) ( $diff / self::cSecondsDay );
			$diff  %= self::cSecondsDay;

			$data[] = (int) ( $diff / self::cSecondsHour );
			$diff  %= self::cSecondsHour;

			$data[] = (int) ( $diff / self::cSecondsMinute );
			$diff  %= self::cSecondsMinute;

			$data[] = $diff;

			return( ( $bWantString ) ? vsprintf( "%d %02d:%02d:%02d", $data ) : $data );
		}

		return( false );
	}

	# PASS IN 'CST', WILL RETURN -6*3600
	# IF CAN'T FIND, RETURNS FALSE
	/**
	 * GetBias takes the letters for timezone offset and returns the seconds of offset.
	 *
	 * @param string $tz CST,PDT
	 * @param boolean $dst If observing DST or not
	 * @return integer|false
	 */
	public function GetBias( $tz, $dst = false )
	{
		$tz = strtolower( $tz );

		foreach( self::$_szTimeZone as $ofs => $letters )
		{
			if( in_array( $tz, $letters ) )
			{
				if( $dst )
					$ofs += 3600;

				return( $ofs );
			}
		}

		return( false );
	}

	# IF TRY TO PRINT OBJECT, WILL PRINT STRING
	/**
	 * PHP Magic function to return a print request of object as a string.
	 * @example print $dttm; prints "2011-12-12 01:02:03"
	 */
	public function __toString()
	{
		return( $this->GetString( self::$FMT_DTTM ) );
	}

	/**
	 * PHP Magic function to call properties like methods
	 *
	 * If you call $dttm->hour it will return $this->tm_hour
	 * @param string $part
	 * @return string|null
	 */
	public function __get( $part )
	{
		$part = 'tm_' . strtolower( $part );

		return( ( isset( $this->$part ) ) ? $this->$part : null );
	}

	/**
	 * PHP Magic function to set properties on this object
	 *
	 * Example would be $dttm->hour = 12; would set tm_hour = 12.
	 *
	 * @param string $part
	 * @param string $value
	 */
	public function __set( $part, $value )
	{
		$part = 'tm_' . strtolower( $part );

		if( $part == 'tm_tzofs' )
			return( $this->SetTZOfs( $value ) );

		if( isset( $this->$part ) )
		{
			$this->$part = $value;
			$this->_Normalize();
		}
	}

	/**
	 * PHP magic function to return if a property is set or not
	 * @param string $part
	 * @return boolean
	 */
	public function __isset( $part )
	{
		$part = strtolower( $part );
		return( isset( $this->$part ) );
	}

	/**
	 * PHP magic function to unset a property on this object.
	 * @param string $part
	 * @return void
	 */
	public function __unset( $part )
	{
		$part = strtolower( $part );
		unset( $this->$part );
	}

	/**
	 * GetMonthName returns the name of the month either abbr. or full
	 * @param boolean $bLong
	 * @return string
	 */
	public function GetMonthName( $bLong = false )
	{
		$sMonthName = ( $bLong ) ? self::$_szLongMonths[$this->month - 1] : self::$_szShortMonths[$this->month - 1];
		return( $sMonthName );
	}

	/**
	 * GetWeekdayName returns the name of the day either abbr. or full
	 * @param boolean $bLong
	 * @return string
	 */
	public function GetWeekdayName( $bLong = false )
	{
		$sWeekdayName = ( $bLong ) ? self::$_szLongDays[$this->DayOfWeek()] : self::$_szShortDays[$this->DayOfWeek()];
		return( $sWeekdayName );
	}

	/**
	 * IsLeapYear tells you if the current year is a leap year or not
	 * @param integer $nYear optional, defaults to current year
	 * @return boolean
	 */
	public function IsLeapYear( $nYear = false )
	{
		if( $nYear === false )
			$nYear = $this->tm_year;
		return( ( ( ( $nYear & 3 ) == 0 && ( ( ( $nYear % 400 ) == 0 ) ^ ( ( $nYear % 100 ) != 0 ) ) ) ) ? true : false );
	}

	/**
	 * IsDST returns if the current object should be observing dst.
	 *
	 * IsDST returns whether or not the date is in daylight saving time
	 * @return boolean
	 */
	public function IsDST()
	{
		return( ( date( 'I', $this->GetUnixTime() ) ) ? true : false );
	}

	/**
	 * IsWeekDay returns true or false if DayOfWeek is Mon-Fri
	 * @return boolean
	 */
	public function IsWeekDay()
	{
		return( ( $this->DayOfWeek( 1 ) < 6 ) ? true : false );
	}

	/**
	 * SetTZOfs handles a variety of values passed in and
	 * tries to determine the TZOffset and sets it.
	 * @param mixed $value
	 */
	public function SetTZOfs( $tm_tzofs = false )
	{
		if( $tm_tzofs === false )
			return false;

		# FIRST: PARSE VALUE, DETERMINE IF ( 21600, CST, +0000, +00:00 ) SECS, TZ, HOURS, HH:MM

		# -06:00
		if( stripos( $tm_tzofs, ':' ) )
		{
			$sign = substr( $tm_tzofs, 0, 1 );
			$hrs  = substr( $tm_tzofs, 1, 3 );
			$min  = substr( $tm_tzofs, 4, 2 );
			$ofs = ( $hrs * self::cSecondsHour ) + ( $min * self::cSecondsMinute );
			$this->tm_tzofs = $sign . $ofs;
			return;
		}

		foreach( self::$_szTimeZone as $ofs => $letters )
		{
			# CST
			if( in_array( $tm_tzofs, $letters ) )
			{
				$this->tm_tzofs = $ofs;
				return;
			}

			# 21600
			if( $tm_tzofs == $ofs )
			{
				$this->tm_tzofs = $tm_tzofs;
				return;
			}
		}
	}

	/**
	 * _Normalize takes and makes sure the dttm is proper.
	 *
	 * Normalize makes sure you do not have hours set to 28 or
	 * minutes set to 300. Advances the date to the correct date time.
	 */
	private function _Normalize()
	{
		# adjust seconds
		while( $this->tm_second < 0 )
		{
			$this->tm_second    += self::cSecondsMinute;
			$this->tm_minute    -= 1;
		}

		while( $this->tm_second >= self::cSecondsMinute )
		{
			$this->tm_second    -= self::cSecondsMinute;
			$this->tm_minute    += 1;
		}

		# adjust minutes
		while( $this->tm_minute < 0 )
		{
			$this->tm_minute    += self::cMinutesHour;
			$this->tm_hour      -= 1;
		}

		while( $this->tm_minute >= self::cMinutesHour )
		{
			$this->tm_minute    -= self::cMinutesHour;
			$this->tm_hour      += 1;
		}

		# adjust hours
		while( $this->tm_hour < 0 )
		{
			$this->tm_hour      += self::cHoursDay;
			$this->tm_day       -= 1;
		}

		while( $this->tm_hour >= self::cHoursDay )
		{
			$this->tm_hour      -= self::cHoursDay;
			$this->tm_day       += 1;
		}

		# ADJUST YEARS LESS THAN 100
		$this->tm_year += ( $this->tm_year < 38 ) ? 2000 : ( ( $this->tm_year < 100 ) ? 1900 : 0 );

		while( true )
		{
			# ADJUST MONTHS
			while( $this->tm_month < 1 )
			{
				$this->tm_month += self::cMonthsYear;
				$this->tm_year      -= 1;
			}

			while( $this->tm_month > self::cMonthsYear )
			{
				$this->tm_month -= self::cMonthsYear;
				$this->tm_year      += 1;
			}

			$nDays = ( self::$_nDaysMonth[$this->tm_month] + ( ( $this->tm_month == 2 && $this->IsLeapYear() ) ? 1 : 0 ) );

			if( $this->tm_day < 1 )
			{
				$this->tm_month -= 1;

				if( $this->tm_month < 1 )
				{
					$this->tm_month += self::cMonthsYear;
					$this->tm_year      -= 1;
				}

				$nDays  = self::$_nDaysMonth[$this->tm_month] + (($this->tm_month == 2 && $this->IsLeapYear()) ? 1 : 0);

				$this->tm_day   += $nDays;
			}
			else if( $this->tm_day > $nDays )
			{
				$this->tm_day   -= $nDays;
				$this->tm_month += 1;
			}
			else
			{
				break;
			}
		}
		// $this->tm_tzofs     = intval( date( 'Z', $this->GetUnixTime() ) );
		$this->tm_dst       = $this->IsDST();
	}

	# !$bISO == sun->0, sat->6
	# $bISO == mon->1, sun->7
	/**
	 * DayOfWeek returns which day of the week it is.
	 *
	 * This function tells you which day of the week it is
	 * by the index number. There are two options, Non ISO which
	 * starts with Sunday as 0 and Saturday as 6, OR, OR,
	 * ISO which states that Monday is 1 and Sunday is 7.
	 * @param boolean $bISO
	 * @return integer
	 */
	public function DayOfWeek( $bISO = false )
	{
		# force to be 0 || 1
		$bISO = ( $bISO ) ? 1 : 0;

		# RETURN RESULT
		return( ( ( $this->GetLongDate() + 1 - $bISO ) % self::cDaysWeek ) + $bISO );
	}

	/**
	 * DayOfYear returns the day number of the year.
	 * @return integer
	 */
	public function DayOfYear()
	{
		$dayNumber = 0;

		for( $nMonth = 1; $nMonth < $this->tm_month; $nMonth++ )
			$dayNumber += self::$_nDaysMonth[$nMonth];

		$dayNumber += $this->tm_day;

		if( $this->tm_month > 2 && $this->IsLeapYear() )
			$dayNumber++;

		return( $dayNumber );
	}

	/**
	 * ISOFirstDayOfYear tells you the first day of the year according to ISO standards
	 *
	 * @param integer $year
	 * @return object
	 */
	private function _ISOFirstDayOfYear( $year )
	{
		$first = new DTTM( "{$year}-01-04 00:00:00 GMT" );
		$first->day -= ( $first->DayOfWeek( true ) - 1 );
		return( $first );
	}

	/**
	 * ISOYear returns which year the day belongs to according to ISO standards.
	 * @return integer
	 */
	public function ISOYear()
	{
		$year = $this->year;

		# CHECK JAN AND DEC FOR POSSIBLE VALUES
		if( $this->month == 1 || $this->month == 12 )
		{
			# GET THE ISO WEEK OF THE YEAR
			$thisWOY = $this->WeekOfYear( true );

			# IF WEEK 1 AND IN DEC THEN USE NEXT YEAR
			if( $thisWOY == 1 && $this->month == 12 )
				$year++;

			# if last week(s) and in Jan then use previous year
			if( $thisWOY >= 52 && $this->month == 1 )
				$year--;
		}

		return( $year );
	}

	/**
	 * WeekOfYear returns the week of the year.
	 *
	 * The function will find either ISO Standard week or
	 * just count from January 1 week.
	 *
	 * @param boolean $ISO
	 * @return integer
	 */
	public function WeekOfYear( $ISO = false )
	{
		# THE FIRST OF THE YEAR
		$thisFDOY = new DTTM( "{$this->year}-01-01 00:00:00 GMT" );

		# WEEK STARTS WITH SUNDAY; 0 BASED
		if( !$ISO )
		{
			$thisFDOY->day -= $thisFDOY->DayOfWeek( false );
		}
		# WEEK STARTS WITH MONDAY; 1 BASED
		else
		{
			$thisFDOY = $this->_ISOFirstDayOfYear( $this->year );
			$nextFDOY = $this->_ISOFirstDayOfYear( $this->year + 1 );

			if( $thisFDOY->GetLongDate() > $this->GetLongDate() )
				$thisFDOY = $this->_ISOFirstDayOfYear( $this->year - 1 );

			if( $nextFDOY->GetLongDate() <= $this->GetLongDate() )
				$thisFDOY = $nextFDOY;
		}

		# DIFFERENCE B/T BOTH DATES
		$weekofyear = intval( ( $this->GetLongDate() - $thisFDOY->GetLongDate() ) / 7 ) + 1;

		return( $weekofyear );
	}

	/**
	 * GetLongDate returns how many days it has been since 0.
	 * @return integer
	 */
	public function GetLongDate()
	{
		$totalDays = 0;
		$year = 1;

		# ADD 400 YEAR PERIODS
		while( ( $year + 400 ) < $this->tm_year )
		{
			$totalDays += self::cDays400Years;
			$year += 400;
		}

		# ADD 100 YEAR PERIODS
		while(( $year + 100 ) < $this->tm_year )
		{
			$totalDays += self::cDays100Years;
			$year += 100;
		}

		# ADD 4 YEAR LEAP PERIODS
		while(( $year + 4 ) < $this->tm_year )
		{
			$totalDays += self::cDays4Years;
			$year += 4;
		}

		# ADD YEARS
		while( $year < $this->tm_year )
		{
			$totalDays += ( self::cDaysYear + ($this->IsLeapYear( $year ) ? 1 : 0) );
			$year++;
		}

		# ADD NUMBER OF DAYS
		$totalDays += ( $this->DayOfYear() - 1 );

		# RETURN DAYS
		return( $totalDays );
	}

	/**
	 * GetLongTime returns an integer of how many seconds it has been since mid-night.
	 * @return integer
	 */
	public function GetLongTime()
	{
		$totalSecs = 0;

		# HOURS
		$totalSecs += ( $this->tm_hour * self::cSecondsHour );

		# MINUTES
		$totalSecs += ( $this->tm_minute * self::cSecondsMinute );

		# SECONDS
		$totalSecs += ( $this->tm_second );

		# RETURN RESULT
		return( $totalSecs );
	}

	/**
	 * GetUnixTime returns how many seconds it has been since the Unix Epoch.
	 *
	 * How many seconds has it been since Jan. 1, 1970. If the date
	 * is before that, it will return negative number.
	 * @return integer
	 */
	public function GetUnixTime()
	{
		$totalDays = 0;
		$totalSeconds = 0;

		# CAN ONLY HANDLE FROM 1902 TO 2038 ...
	#       if( $this->tm_year < 1902 || $this->tm_year > 2038 )
	#           return( 0 );

		$totalSeconds = ( $this->GetLongDate() - self::cDaysTo1970 ) * self::cSecondsDay;

		if( $this->tm_year < 1970 )
			$totalSeconds -= ( self::cSecondsDay - $this->GetLongTime() ) - self::cSecondsDay;
		else
			$totalSeconds += $this->GetLongTime();

		if( $this->tm_tzofs )
			$totalSeconds -= $this->tm_tzofs;

		return( $totalSeconds );
	}

	/**
	 * GetOleDateTime returns how many days it has been since 12/30/1899 00:00:00.
	 *
	 * The integer part is days the decimal part is the time.
	 * @return float
	 */
	public function GetOleDateTime()
	{
		$lDays  = $this->GetLongDate();
		$lSecs  = $this->GetLongTime();
		$dResult = ($lDays - 693593); # 12/30/1899 00:00:00

		# FOR DAYS BELOW THE EPOCH THE TIME IS SUBTRACTED ???
		if( $dResult >= 0.0 )
			$dResult += ($lSecs / self::cSecondsDay);
		else
			$dResult -= ($lSecs / self::cSecondsDay);

		return( $dResult );
	}

	/**
	 * GetLocalTime returns a hash of the date object.
	 *
	 * Returns a hash of the dttm object with the year
	 * being from 1900 and the month is 0 based.
	 * @return hash
	 */
	public function GetLocalTime()
	{
		return( array(
			'tm_year'   => $this->tm_year - 1900,
			'tm_mon'    => $this->tm_month - 1,
			'tm_mday'   => $this->tm_day,
			'tm_hour'   => $this->tm_hour,
			'tm_min'    => $this->tm_minute,
			'tm_sec'    => $this->tm_second,
			'tm_wday'   => $this->DayOfWeek(),
			'tm_yday'   => $this->DayOfYear(),
		) );
	}

	/**
	 * GetDateTime returns either an array or a string of the current date time.
	 *
	 * @param boolean $bWantArray
	 * @return array|string
	 */
	function GetDateTime( $bWantArray = false )
	{
		$time = array( $this->tm_year, $this->tm_month, $this->tm_day, $this->tm_hour, $this->tm_minute, $this->tm_second );
		return( ( $bWantArray ) ? $time : vsprintf( "%04d-%02d-%02d %02d:%02d:%02d", $time ) );
	}

	/**
	 * SetDate tries to set the dttm object to the date passed in.
	 *
	 * This function can take a dttm object, an array, a string, or
	 * false and will parse it and set it to the dttm.
	 *
	 * @param object|array|boolean|string
	 * @param integer $tzofs
	 */
	public function SetDate( $datetime, $isGMT = false, $tzofs = false )
	{

		# Copy
		if( $datetime instanceof DTTM )
		{
			$this->tm_year      = $datetime->tm_year;
			$this->tm_month     = $datetime->tm_month;
			$this->tm_day       = $datetime->tm_day;
			$this->tm_hour      = $datetime->tm_hour;
			$this->tm_minute    = $datetime->tm_minute;
			$this->tm_second    = $datetime->tm_second;
			$this->tm_tzofs     = ( $tzofs !== false ) ? $tzofs : $datetime->tm_tzofs;
			$this->tm_dst       = $this->IsDST();
			return( true );
		}

		if( is_array( $datetime ) )
		{
			$this->tm_year      = $datetime[0];
			$this->tm_month     = $datetime[1];
			$this->tm_day       = $datetime[2];
			$this->tm_hour      = $datetime[3];
			$this->tm_minute    = $datetime[4];
			$this->tm_second    = $datetime[5];
			$this->_Normalize();
			$this->tm_tzofs     = ( $tzofs !== false ) ? $tzofs : self::$TZOFS; // intval( date( 'Z', $this->GetUnixTime() ) );
			$this->tm_dst       = $this->IsDST();
			if( !$isGMT )
				$this->second -= $this->tm_tzofs;
			return( true );
		}

		$this->tm_year      = 1970;
		$this->tm_month     = 1;
		$this->tm_day       = 1;
		$this->tm_hour      = 0;
		$this->tm_minute    = 0;
		$this->tm_second    = 0;

		# NOT SET
		if( $datetime === false || strlen( $datetime ) == 0 )
		{
			$datetime = time();
			$isGMT = true;
		}
		# A STRING
		else if( !is_numeric( $datetime ) )
		{
			if( $parseDTTM = self::Parse( $datetime, $isGMT, $tzofs ) )
			{
				$this->SetDate( $parseDTTM, $isGMT, $tzofs );
				return( true );
			}

			return( false );
		}

		# IF BEFORE EPOC
		if( $datetime < 0 )
		{
			# WHILE NEG., SUB YEARS AND ADD TO DATETIME A YR IN SECONDS
			while( $datetime < 0 )
			{
				$this->tm_year -= 1;
				$datetime += ( ( self::cDaysYear + $this->IsLeapYear() ) * self::cSecondsDay );
			}
		}

		# YEAR
		while( true )
		{
			# SECONDS IN THE YEAR
			$secondsYear = ( ( self::cDaysYear + $this->IsLeapYear() ) * self::cSecondsDay );

			# IF REMOVED ALL THE YEARS FROM THE TIME, QUIT
			if( $datetime < $secondsYear )
				break;

			# SUBSTRACT SECONDS FROM DATETIME
			$datetime -= $secondsYear;
			$this->tm_year++;
		}

		# MONTH
		while( true )
		{
			# GET COUNT ON DAYS IN MONTH
			$days = ( self::$_nDaysMonth[$this->tm_month] + ( ( $this->tm_month == 2 && $this->IsLeapYear() ) ? 1 : 0 ) );

			# GET TOTAL SECONDS IN MONTH
			$sMonth = $days * self::cSecondsDay;

			# IF NO MORE MONTHS IN DATETIME, QUIT
			if( $datetime < $sMonth )
				break;

			# REMOVE MONTH FROM DATETIME AND ADD A MONTH
			$datetime -= $sMonth;
			$this->tm_month++;
		}

		# DAY
		$this->tm_day = intval( $datetime / self::cSecondsDay ) + 1;
		$datetime = intval( $datetime % self::cSecondsDay );

		# HOUR
		$this->tm_hour = intval( $datetime / self::cSecondsHour );
		$datetime = intval( $datetime % self::cSecondsHour );

		# MINUTE
		$this->tm_minute = intval( $datetime / self::cSecondsMinute );
		$datetime = intval( $datetime % self::cSecondsMinute );

		# SECOND
		$this->tm_second = intval( $datetime );

		$this->_Normalize();
		$this->tm_tzofs     = ( $tzofs !== false ) ? $tzofs : self::$TZOFS; // intval( date( 'Z', $this->GetUnixTime() ) );
		$this->tm_dst       = $this->IsDST();
		# adjust to GMT
		if( !$isGMT )
			$this->second -= $this->tm_tzofs;

	}

	/**
	 * SetTime takes hours, minutes, and seconds and sets the object to that time.
	 *
	 * Has defaults to set it to 0 for each hours, minutes, and seconds.
	 * @param integer $nHrs
	 * @param integer $nMins
	 * @param integer $nSecs
	 */
	function SetTime( $nHrs = 0, $nMins = 0, $nSecs = 0 )
	{
		$this->tm_hour   = (int)( $nHrs );
		$this->tm_minute = (int)( $nMins );
		$this->tm_second = (int)( $nSecs );

		$this->_Normalize();
	}

	/**
	 * GetTimeZone returns the current time zone the dttm object is in.
	 * @param boolean $label If to return the timezone label. CDT, PST, etc.
	 * @param boolean $separate If to return the timezone offset with a seperator. 06:00 or 0600
	 * @return string
	 */
	public function GetTimeZone( $label = true, $separate = false )
	{
		$ofs = '';
		$sign = ( $this->tm_tzofs >= 0 ) ? '+' : '-';
		$secs = abs( $this->tm_tzofs );
		$lbl = '';

		# GMT, UTC, CST, EST
		if( $label )
		{
			if( $this->IsDST() )
			{
				$lbl = ( isset( self::$_szTimeZone[( $this->tm_tzofs - self::cSecondsHour )][1] ) ) ?
						self::$_szTimeZone[( $this->tm_tzofs - self::cSecondsHour )][1] :
						self::$_szTimeZone[$this->tm_tzofs][0];
			}
			else
				$lbl = self::$_szTimeZone[$this->tm_tzofs][0];

			return( strtoupper( $lbl ) );
		}
		# HH:SS
		else
		{
			$hrs = $secs / 3600;
			$sec = $secs % 3600;

			if( $separate )
			{
				$ofs = sprintf( "%02d", $hrs ) . ":" . sprintf( "%02d", $sec );
			}
			else
			{
				$sec = ( $sec / 60 ) * 100;
				$ofs = sprintf( "%02d", $hrs ) . sprintf( "%02d", $sec );
			}
		}

		$ofs = $sign . $ofs;

		return( $ofs );
	}

	/**
	 * GetDaysInMonth returns number of days in the month.
	 * @param integer $month Optional.
	 * @return integer
	 */
	public function GetDaysInMonth( $month = false )
	{
		$total_days = 0;

		if( !$month )
			$month = $this->tm_month;

		$total_days = self::$_nDaysMonth[$month];

		if( $month == 2 && $this->IsLeapYear() )
			$total_days++;

		return( $total_days );
	}

	/**
	 * GetDaySuffix returns the language end of the dates.
	 *
	 * @return string
	 */
    public function GetDaySuffix()
    {
        $suffixes   = array( 'th', 'st', 'nd', 'rd' );
        $s          = $suffixes[0];
        $hund       = $this->day % 100;
        $ten        = $this->day % 10;
        if( !( $hund >= 11 && $hund <=13 ) && $ten < 4 )
            $s = $suffixes[$ten];

        return( $s );
    }

	/**
	 * GetPart takes a letter and returns that part of the date object.
	 *
	 * GetPart works on the same letters as defined in php's date function.
	 * @param string $part
	 * @return string
	 */
	public function GetPart( $part )
	{
		switch( $part )
		{
		## Year
			# Whether it's a leap year
			case 'L': return( $this->IsLeapYear() );
			# A two digit representation of a year
			case 'y': return( sprintf( "%02d", $this->tm_year % 100 ) );
			# A full numeric representation of a year, 4 digits
			case 'Y': return( sprintf( "%04d", $this->tm_year ) );
			# ISO Year
			case 'o': return( $this->ISOYear() );

		## Month
			# A full textual representation of a month, such as January or March
			case 'F': return( self::$_szLongMonths[$this->tm_month - 1] );
			# A short textual representation of a month, three letters
			case 'M': return( self::$_szShortMonths[$this->tm_month - 1] );
			# Numeric representation of a month, with leading zeros
			case 'm': return( sprintf( "%02d", $this->tm_month ) );
			# Numeric representation of a month, without leading zeros
			case 'n': return( $this->tm_month );
			# Number of days in the given month
			case 't': return( $this->GetDaysInMonth() );

		## Week
			case 'W': return( sprintf( "%02d", $this->WeekOfYear( true ) ) ); #ISO
			case 'C': return( sprintf( "%02d", $this->WeekOfYear( false ) ) ); #Calendar

		## Day
			# Day of the month, 2 digits with leading zeros
			case 'd': return( sprintf( "%02d", $this->tm_day ) );
			# A textual representation of a day, three letters
			case 'D': return( self::$_szShortDays[$this->DayOfWeek()] );
			# Day of the month without leading zeros
			case 'j': return( $this->tm_day );
			# A full textual representation of the day of the week
			case 'l': return( self::$_szLongDays[$this->DayOfWeek()] );
			# ISO-8601 numeric representation of the day of the week 1 - Mon, 7 - Sun
			case 'N': return( $this->DayOfWeek( true ) );
			# Numeric representation of the day of the week 0 - Sun, 6 - Sat
			case 'w': return( $this->DayOfWeek() );
			# The day of the year ( starting from 0 - 365 )
			case 'z': return( $this->DayOfYear() - 1 );
			case 'S': return( $this->GetDaySuffix() );

		## Time
			# Lowercase Ante meridiem and Post meridiem
			case 'a': return( ( ( $this->tm_hour < 12 ) ? "am" : "pm" ) );
			# Uppercase Ante meridiem and Post meridiem
			case 'A': return( ( ( $this->tm_hour < 12 ) ? "AM" : "PM" ) );

			# Swatch Internet time
			case 'B': return( sprintf( "%03d", intval( ( ( $this->GetLongTime() - $this->tzofs + self::cSecondsHour ) % self::cSecondsDay / self::cSecondsDay ) * 1000 ) ) );
			# 24-hour format of an hour without leading zeros
			case 'G': return( intval( $this->hour ) );
			# 12-hour format of an hour without leading zeros
			case 'g': return( ( ( ( $this->tm_hour + 11 ) % 12 ) + 1 ) );

			# 12-hour format of an hour with leading zeros
			case 'h': return( sprintf( "%02d", $this->GetPart( 'g' ) ) );
			# 24-hour format of an hour with leading zeros
			case 'H': return( sprintf( "%02d", $this->tm_hour ) );

			# Minutes with leading zeros
			case 'i': return( sprintf( "%02d", $this->tm_minute ) );
			# Seconds, with leading zeros
			case 's': return( sprintf( "%02d", $this->tm_second ) );

		## Timezones
			# Whether or not the date is in daylight saving time
			case 'I': return( $this->IsDST() );

			# Timezone abbreviation
			case 'T': return( $this->GetTimeZone() );
			# Difference to Greenwich time (GMT) with colon between hours and minutes
			case 'P': return( $this->GetTimeZone( false, true ) );
			# Difference to Greenwich time (GMT) in hours
			case 'O': return( $this->GetTimeZone( false ) );
			# Timezone offset in seconds.
			case 'Z': return( $this->tm_tzofs );
			# Timezone identifier
			case 'e': return( date( 'e', $this->GetUnixTime() ) );

		## PRE FORMATTED
			# ISO-8601
			case 'c': return( $this->GetString( "Y-m-d\\TH:i:sP" ) ); # DATE_ISO8601
			# RFC2822
			case 'r': return( $this->GetString( DATE_RFC2822 ) );
			# Timestamp
			case 'U': return( $this->GetUnixTime() );

			default: return( $part );
		}
	}

	/**
	 * GetString takes a date format string and returns the dttm part.
	 * @param string $format
	 * @param integer $adjustGMT
	 * @return string
	 */
	public function GetString( $format = false, $useTZ = true )
	{
		if( $format === false )
			$format = self::$FMT_DTTM;
		$chars = str_split( $format );
		$result = '';
		$tm_tzofs = $this->tm_tzofs;

		if( $useTZ )
			$this->second += $this->tm_tzofs;
		else
			$this->tm_tzofs = 0;


		for( $i = 0; $i < count( $chars ); $i++ )
			$result .= ( $chars[$i] == '\\' ) ? $chars[++$i] : $this->GetPart( $chars[$i] );
		if( $useTZ )
			$this->second -= $this->tm_tzofs;
		else
			$this->tm_tzofs = $tm_tzofs;

		return( $result );
	}

	/**
	 * Parse is a static function designed to handle a variety of date strings
	 * @param string $sz
	 * @return object|null
	 */
	static public function Parse( $sz, $isGMT = false, $tzofs = false )
	{
		$rc = array( 1900, 1, 1, 0, 0, 0 );
		$months = implode( '|', self::$_szShortMonths );

		// pattern, year, month, day
		$tests = array(
			// yyyy/mm/dd
			array( "^(\\d{4})[^\\w:](\\d{1,2})[^\\w:](\\d{1,2})", 1, 2, 3 ),
			// mm/dd/yy[yy]
			array( "^(\\d{1,2})[^\\w:](\\d{1,2})[^\\w:](\\d{1,4})", 3, 1, 2 ),
			// dd mon year
			array( "^(\\d{1,2})[^\\w:]+(" . $months . ")\\w*[^\\w:]+(\\d{2,4})", 3, 2, 1 ),
			// mon dd year
			array( "^(" . $months . ")\\w*[^\\w:]+(\\d{1,2})[^\\w:]+(\\d{2,4})", 3, 1, 2 ),
			// month year
			array( "^(" . $months . ")\\w*[^\\w:]+(\\d{2,4})", 2, 1, null ),
		);

		$bOK = false;

		for( $t = 0; $t < count( $tests ); $t++ )
		{
			preg_match( "/{$tests[$t][0]}/i", $sz, $dt );

			if( $dt )
			{
				for( $r = 0; $r < 3; $r++ )
				{
					$szdt = $dt[$tests[$t][$r+1]];

					if( !$szdt )
						$szdt = 1;

					if( $r == 1 && !is_numeric( $szdt ) ) // ascii month
					{
						for( $m = 0; $m < 12; $m++ )
						{
							if( strtoupper( $szdt ) == strtoupper( self::$_szShortMonths[$m] ) )
							{
								$rc[$r] = $m + 1;
								break;
							}
						}
					}
					else
					{
						// remove leading zeros and assign
						$rc[$r] = ltrim( $szdt, '0' );
					}
				}

				if( $rc[0] < 100 )
				{
					$now = new DTTM();
					$rc[0] += intval( $now->GetString( 'Y' ) / 100 ) * 100;

					if( ( $now->GetString('Y') - $rc[0] ) > 20 )
						$rc[0] += 100;

					if( $rc[0] - 20 > $now->GetString('Y') )
						$rc[0] -= 100;
				}

				// validation
				$bOK = ( ( $rc[1] >= 1 && $rc[1] <= 12 ) && ( $rc[2] >= 1 && $rc[2] <= 31 ) ) ? true : false;
				break;
			}
		}

		// 10:21[:21][[A|M]P] [+-xx:xx]
		// 1-3 h:m:s
		// 4 am|pm
		// 5 tz
		$re = "(\\d{1,2}):(\\d{1,2}):?(\\d{1,2})?\\.?\\d*\\s*([AP]M)?\s*([+-]\\d{1,2}:?\\d{1,2})?$";

		preg_match( "/{$re}/i", $sz, $tm );

		if( $tm )
		{
			for( $r = 0; $r < 3 && isset( $tm[$r+1] ); $r++ ) // remove leading zeros and assign
				$rc[$r+3] = intval( preg_replace( "/^0+(\d)/", "$1", $tm[$r+1] ) );

			if( isset( $tm[4] ) && strtoupper( $tm[4] ) == 'PM' && $rc[3] < 12 )
				$rc[3] += 12;

			if( $tzofs === false && isset( $tm[5] ) && $tm[5] ) // timezone
				$tzofs = intval( str_replace( ':', '', $tm[5] ) ) * 36;
			// validation
			$bOK = true;
		}

		return( ( $bOK ) ? new DTTM( $rc, $isGMT, $tzofs ) : null );
	}


	/**
	 * Fetch works off date set on object and parses about any English textual datetime description
	 *
	 * @param string $sTarget +1 week, next week, +5 days, etc
	 * @return object|false
	 */
	public function Fetch( $sTarget )
	{
		static $szLongDays     = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );

		static $szLongMonths   = array( 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' );

		$modify = array();

		static $txtmods = array(
					'yesterday' => 'yesterday',
					'tomorrow'  => 'tomorrow',
					'today'     => 'today',
					'previous'  => -1,
					'first'     => 'first',
					'this'      => 1,
					'next'      => 1,
					'second'    => 'second',
					'third'     => 'third',
					'fourth'    => 'fourth',
					'last'      => 'last',
					);

		# figure out what modifier they want
		$string = '/^(yesterday|tomorrow|today|previous|this|next|first|second|third|fourth|last|\+|-)?(\d+)?/i';
		preg_match( $string, $sTarget, $parse );

		if( $parse )
		{
			# + 2
			if( isset( $parse[2] ) && $parse[2] != '' )
			{
				$modify[0] = intval( ( ( $parse[1] == "" ) ? "+" : $parse[1] ) . $parse[2] );
			}
			# next, first, etc
			else
			{
				if( $parse[1] != '' )
					$modify[0] = $txtmods[strtolower( $parse[1] )];
				else
					return( false );
			}
		}
		else
			return( false );

		# HANDLE SINGLE WORD
		if( in_array( $modify[0], array( 'yesterday', 'tomorrow', 'today' ) ) )
		{
			$modify[0] = strtolower( $modify[0] );

			$dt = new DTTM( $this );
			$dt->SetTime();

			if( $modify[0] == 'yesterday' )
			{
				$dt->tm_day--;
			}
			else if( $modify[0] == 'tomorrow' )
			{
				$dt->tm_day++;
			}

			$dt->_Normalize();

			return( $dt );
		}

		# now find what time they want to modify
		$string = '/(second|minute|hour|day|week|month|year|' . implode( "|", $szLongDays ) . '|' . implode( "|", $szLongMonths ) . ')s?$/i';
		preg_match( $string, $sTarget, $parse );

		if( $parse )
			$modify[1] = strtolower( $parse[1] );
		else
			return( false );

		#### IF HERE, WE PARSED STRING, DO SOMETHING!!

		$dt = new DTTM( $this );

		#FIRST - check to see if passing last or first
		if( in_array( $modify[0], array( 'first', 'second', 'third', 'fourth' ) ) ) # first monday
		{
			# day of week looking for
			$dow = array_search( strtolower( $modify[1] ), $szLongDays );

			if( is_int( $dow ) )
			{
				$dt->tm_day = 1;

				while( $dt->DayOfWeek( false ) != $dow )
					$dt->day += 1;

				if( $modify[0] != 'first' )
				{
					$factors = array(
							'second' => 1,
							'third' => 2,
							'fourth' => 3 );

					$dt->day += ( $factors[$modify[0]] * 7 );
				}
			}
		}
		else if( $modify[0] == 'last' ) # last friday
		{
			# day of week looking for
			$dow = array_search( strtolower( $modify[1] ), $szLongDays );

			if( is_integer( $dow ) )
			{
				$dt->tm_day = self::$_nDaysMonth[$dt->tm_month];

				if( $dt->tm_month == 2 )
					$dt->tm_day += $dt->IsLeapYear();

				while( $dt->DayOfWeek( false ) != $dow )
					$dt->tm_day -= 1;
			}
			else if( $modify[1] == 'day' )
			{
				$dt->tm_day = self::$_nDaysMonth[$dt->tm_month];

				if( $dt->tm_month == 2 )
					$dt->tm_day += $dt->IsLeapYear();
			}
			else if( $modify[1] == 'month' )
			{
				$dt->month--;
			}
		}
		## From here, $modify[0] should be an integer
		else if( in_array( $modify[1], array( 'second', 'minute', 'hour', 'day', 'month', 'year' ) ) )
		{
			$name = 'tm_' . $modify[1];
			$dt->$name += $modify[0];
		}
		else if( $modify[1] == 'week' )
		{
			$dt->tm_day += ( 7 * $modify[0] );
		}
		else if( in_array( strtolower( $modify[1] ), $szLongDays ) )
		{
			$mod_dow = array_search( strtolower( $modify[1] ), $szLongDays );

			if( $modify[0] > 0 )
			{
				# CATCH EX. DATE = SUNDAY, WANT NEXT SUNDAY - ALLOW TO GO AHEAD
				if( $dt->DayOfWeek( false ) == $mod_dow )
					$dt->day += 1;

				while( $dt->DayOfWeek( false ) != $mod_dow )
					$dt->tm_day += 1;

				if( $modify[0] > 1 )
					$dt->tm_day += ( 7 * ( $modify[0] - 1 ) );
			}
			else
			{
				# CATCH EX. DATE = SUNDAY, WANT PREVIOUS SUNDAY - ALLOW TO GO BACK A WEEK
				if( $dt->DayOfWeek( false ) == $mod_dow )
					$dt->day -= 1;

				while( $dt->DayOfWeek( false ) != $mod_dow )
					$dt->tm_day -= 1;

				if( $modify[0] < -1 )
					$dt->tm_day -= ( 7 * ( abs( $modify[0] + 1 ) ) );
			}
		}
		else if( in_array( strtolower( $modify[1] ), $szLongMonths ) ) #next January
		{
			$mod_moy = array_search( strtolower( $modify[1] ), $szLongMonths ) + 1;

			if( $modify[0] > 0 )
				$dt->tm_year += $modify[0];
			else
				$dt->tm_year -= $modify[0];

			$dt->tm_month = $mod_moy;
		}

		$dt->_Normalize();

		return( $dt );
	}

	/**
	 * Format is a static function to parse a dttm string and format it properly.
	 *
	 * If no date is passed in returns false.
	 * 2014-10-31 , 10-31-2014 Oct. 31, 2014
	 * @example DTTM::Format( '2014-01-01 12:13:01', $ini['APP']['FMT_DT'], false, true )
	 * @param string $sDate
	 * @param string $sFormat
	 * @param boolean $isGMT
	 * @return boolean $useTZ
	 */
	static function Format( $sDate = '', $sFormat = false, $isGMT = true, $useTZ = true )
	{
		if( $sFormat === false )
			$sFormat = self::$FMT_DTTM;

		if( $sDate && $dt = new DTTM( $sDate, $isGMT ) )
			return( $dt->GetString( $sFormat, $useTZ ) );

		return( false );
	}

	function Relative( $sRel )
	{
		$dt = $this->Fetch( $sRel );
		return( ( $dt ) ? $dt : false );
	}

    # returns T|F and modifies current Obj

	/**
	 * CalcNextRun takes a pattern and modifies the dttm object to the calculated dttm
	 *
	 * @param string $freq
	 * @param integer $runs
	 * @return boolean
	 */
	function CalcNextRun( $freq , $runs )
	{
		static $szLongDays = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );

		// [otdwmy],#[,#][,#]:[012][,??]
		$segment    = explode( ':', $freq );
		$pattern    = explode( ',' , $segment[0] );
		$range      = explode( ',' , $segment[1] );
		$patterns = array( "first" , "second", "third", "fourth", "last" );
		$dt = new DTTM( $this );
		$dtnow = new DTTM();
		$loops = 0;

		while( true )
		{
			// make time reference to current date/time
			switch( $pattern[0] )
			{
				# Once
				case 'o':
					return( false );
					break;

				# Time: 1 = hours, 2 = minutes
				case 't':
					if( $pattern[1] )
						$dt->hour += $pattern[1];

					if( $pattern[2] )
						$dt->minute += $pattern[2];
					break;

				case 'd':   // Day: 1 = Days
					if( $pattern[1] )
						$dt->day += $pattern[1];
					break;

				case 'w':   // Week: 1 = Weeks, 2 = '0-6'
					if( strlen( $pattern[1] ) && strlen( $pattern[2] ) )
					{
						$weekly_days = str_split( $pattern[2] );
						$curr_day_week = $dt->DayOfWeek( false );
						sort( $weekly_days );

						// find next weekday wanted
						$next_day_week = reset($weekly_days);
						do
						{
							if( $next_day_week > $curr_day_week )
								break;

						} while( $next_day_week = next( $weekly_days ) );

						if ( $next_day_week === false )
						{
							$number_days = $curr_day_week - $weekly_days[0];
							$dt = $dt->Fetch( "+{$pattern[1]} weeks" );
							$dt->day -= $number_days;
						}
						else
						{
							$number_days = $next_day_week - $curr_day_week;
							$dt->day += $number_days;
						}
					}
					break;

				case 'm':   // Month: 1 = type
					switch( $pattern[1] )
					{
						case 1: // 2 = day, 3 = months
							if( strlen( $pattern[2] ) && $pattern[3] )
							{
								$dt->day = $pattern[2];
								$dt->month += $pattern[3];
							}
							break;

						case 2: // 2 = pattern, 3 = weekday, 4 = months
							if( $patterns[$pattern[2]] && $szLongDays[$pattern[3]] && $pattern[4] )
							{
								// first day of month + X months
								$dt->month += $pattern[4];
								$dt->day = 1;
								$dt = $dt->Fetch( "{$patterns[$pattern[2]]} " . $szLongDays[$pattern[3]] );
							}
							break;
					}
					break;

				case 'y':   // year: 1 = type
					switch( $pattern[1] )
					{
						case 1: // 2 = month, 3 = day
							if( strlen( $pattern[2] ) && strlen( $pattern[3] ) )
							{
								$dt->tm_month = $pattern[2];
								$dt->tm_day = $pattern[3];
								if( $loops )
									$dt->tm_year += 1;
								$dt->_Normalize();
							}
							break;

						case 2:  // 2 = pattern, 3 = weekday, 4 = month
							if( $patterns[$pattern[2]] && $szLongDays[$pattern[3]] && $pattern[4] )
							{
								// first day of month + 1 year
								if( $loops )
									$dt->tm_year += 1;
								$dt->tm_month = $pattern[4];
								$dt->tm_day = 1;
								$dt->_Normalize();
								$dt = $dt->Fetch( "{$patterns[$pattern[2]]} " . $szLongDays[$pattern[3]] );
							}
							break;
					}
					break;
			} #ENDSWITCH

			switch( $range[0] )
			{
				case 1: // no end date
					break;

				case 2: // # of times
					if( $runs > $range[1] )
						return( false );
					break;

				case 3: // end date
					if( $dt->GetUnixTime() > $range[1] )
						return( false );
					break;
			}

			// if no next date or next is greater than current time then get out
			if( $dt->GetUnixTime() > $dtnow->GetUnixTime() && $dt->GetUnixTime() > $this->GetUnixTime() )
				break;

			$loops++;
		}

		return( $dt );
	}

	static function SetDaysText( $aShort = false, $aLong = false )
	{
		if( is_array( $aShort ) && count( $aShort ) == 7 )
			self::$_szShortDays = $aShort;
		if( is_array( $aLong ) && count( $aLong ) == 7 )
			self::$_szLongDays = $aLong;
	}
	static function SetMonthsText( $aShort = false, $aLong = false )
	{
		if( is_array( $aShort ) && count( $aShort ) == 12 )
			self::$_szShortMonths = $aShort;
		if( is_array( $aLong ) && count( $aLong ) == 12 )
			self::$_szLongMonths = $aLong;
	}

	static function GetDaysText( $bLong = false )
	{
		return ( $bLong ) ? self::$_szLongDays : self::$_szShortDays;
	}

	static function GetMonthsText( $bLong = false )
	{
		return ( $bLong ) ? self::$_szLongMonths : self::$_szShortMonths;
	}

	static function Now()
	{
		return (new DTTM())->GetString( 'Y-m-d H:i:s' );
		// return gmdate( 'Y-m-d H:i:s' );   //UTC
	}
}
