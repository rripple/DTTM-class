
import datetime
import math
import re

class DTTM:

	cSecondsMinute = 60
	cMinutesHour   = 60
	cSecondsHour   = 3600
	cHoursDay      = 24
	cMinutesDay    = 1440
	cSecondsDay    = 86400
	cMonthsYear    = 12
	cDaysWeek      = 7
	cDaysYear      = 365
	cDays4Years    = 1461
	cDays100Years  = 36524
	cDays400Years  = 146097
	cDaysTo1970    = 719162

	# array _nDaysMonths 1 based array to hold days in each month
	_nDaysMonth    = [ 0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ]

	# array _szShortDays Abbreviated names of days of the week
	_szShortDays   = [ 'Sun', 'Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat' ]

	# array _szLongDays Full names of Days of the week
	_szLongDays    = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ]

	# array _szShortMonths Abbr. names of the months
	_szShortMonths = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec' ]

	# array _szLongMonths List of full names of the months
	_szLongMonths  = [ 'January', 'Febuary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ]

	# array _szTimeZone List of offsets to timezone names
	_szTimeZone    = {}

	# integer TZOFS Seconds offset from GMT
	TZOFS          = 0

	# boolean ISDST If is Daylight Savings or Not on client computer
	ISDST          = False

	# string FMT_DTTM format of datetime
	FMT_DTTM       = "Y-m-d H:i:s"

	def __init__( self, dt = '', isGMT = False, tzofs = False ):

		# integer _year Holds the year number of the date
		self._year = 0

		# integer _month Holds the month number of the date
		self._month = 0

		# integer _day Holds the day number of the date
		self._day = 0

		# integer _hour Holds the hour number of the date
		self._hour = 0

		# integer _minute Holds the minute number of the date
		self._minute = 0

		# integer _second Holds the second number of the date
		self._second = 0

		# integer _tzofs Holds the timezone offset number of the date
		self._tzofs = 0

		# integer _dst Holds the daylight savings boolean of the date
		self._dst = 0

		# SET CONSTANTS
		if not self._szTimeZone:
			self._szTimeZone = {
				0            : [ 'gmt' ],  # Greenwich Mean
				-1*3600      : [ 'wat' ],  # West Africa
				-2*3600      : [ 'at' ],   # Azores
				-4*3600      : [ 'ast', 'adt' ],   # Atlantic Standard
				-5*3600      : [ 'est', 'edt' ],   # Eastern Standard
				-6*3600      : [ 'cst', 'cdt' ],   # Central Standard
				-7*3600      : [ 'mst', 'mdt' ],   # Mountain Standard
				-8*3600      : [ 'pst', 'pdt' ],   # Pacific Standard
				-9*3600      : [ 'yst', 'ydt' ],   # Yukon Standard
				-10*3600     : [ 'hst', 'hdt' ],   # Hawaii Standard
				-11*3600     : [ 'nt' ],   # Nome
				-12*3600     : [ 'idlw' ], # International Date Line West
				+1*3600      : [ 'cet', 'cedt' ],  # Central European
				+2*3600      : [ 'eet', 'eedt' ],  # Eastern Europe, USSR Zone 1
				+3*3600      : [ 'bt' ],   # Baghdad, USSR Zone 2
				+4*3600      : [ 'zp4' ],  # USSR Zone 3
				+5*3600      : [ 'zp5' ],  # USSR Zone 4
				+5*3600+1800 : [ 'ist' ], # Indian Standard
				+6*3600      : [ 'zp6' ],  # USSR Zone 5
				+6*3600+1800 : [ 'nst' ], #North Sumatra
				+7*3600      : [ 'wast', 'wadt' ], # West Australian Standard
				+8*3600      : [ 'cct' ],  # China Coast, USSR Zone 7
				+9*3600      : [ 'jst' ],  # Japan Standard, USSR Zone 8
				+10*3600     : [ 'east', 'eadt' ], # Eastern Australian Standard
				+12*3600     : [ 'nzt', 'ndzt' ],  # New Zealand
			}

#			// get system strings for months and days
#			da = 1
#
#			// find last day of month .. crude .. but safe
#			while( date( "w", mktime( 0, 0, 0, 1, da, 2000 ) ) != 0 )
#				da++
#
#			for( i = 0 i < 7 i++ )
#			{
#				self._szShortDays[i] = strftime( "%a", mktime( 0, 0, 0, 1, da + i, 2000 ) )
#				self._szLongDays[i] = strftime( "%A", mktime( 0, 0, 0, 1, da + i, 2000 ) )
#			}
#
#			for( mo = 1 mo <= 12 mo++ )
#			{
#				self._szShortMonths[mo-1] = strftime( "%b", mktime( 0, 0, 0, mo, 1, 2000 ) )
#				self._szLongMonths[mo-1] = strftime( "%B", mktime( 0, 0, 0, mo, 1, 2000 ) )
#			}

#		if( self.TZOFS == 0 )
#		{
#			self.TZOFS = intval( date( 'Z', time() ) )
#		}

		self.SetDate( dt, isGMT, tzofs )

	""" * GETTERS AND SETTERS FOR DTTM * """

	# YEAR
	@property
	def year( self ):
		return self._year

	@year.setter
	def year( self, n ):
		self._year = n
		self._Normalize()

	# MONTH
	@property
	def month( self ):
		return self._month

	@month.setter
	def month( self, n ):
		self._month = n
		self._Normalize()

	# DAY
	@property
	def day( self ):
		return self._day

	@day.setter
	def day( self, n ):
		self._day = n
		self._Normalize()

	# HOUR
	@property
	def hour( self ):
		return self._hour

	@hour.setter
	def hour( self, n ):
		self._hour = n
		self._Normalize()

	# MINUTE
	@property
	def minute( self ):
		return self._minute

	@minute.setter
	def minute( self, n ):
		self._minute = n
		self._Normalize()

	# SECOND
	@property
	def second( self ):
		return self._second

	@second.setter
	def second( self, n ):
		self._second = n
		self._Normalize()

	# TZOFS
	@property
	def tzofs( self ):
		return self._tzofs

	@tzofs.setter
	def tzofs( self, n ):
		self._tzofs = n

	# DST
	@property
	def dst( self ):
		return self._dst

	@dst.setter
	def dst( self, n ):
		self._dst = n

	def SetDate( self, sDT, isGMT = False, tzofs = 0 ):
		"""
		SetDate tries to set the dttm object to the date passed in.

		This function can take a dttm object, an array, a string, or false and will parse it and set it to the dttm.

		:param object|array|boolean|string
		:param boolean
		:param integer tzofs
		:return boolean
		"""

		# Copy
		if( isinstance( sDT, DTTM ) ):
			self._year   = sDT.year
			self._month  = sDT.month
			self._day    = sDT.day
			self._hour   = sDT.hour
			self._minute = sDT.minute
			self._second = sDT.second
			self._tzofs  = tzofs if ( tzofs != False ) else sDT.tzofs
			self._dst    = self.IsDST()
			self._Normalize()

			return True

		if( isinstance( sDT, list ) ):
			self._year      = sDT[0]
			self._month     = sDT[1]
			self._day       = sDT[2]
			self._hour      = sDT[3]
			self._minute    = sDT[4]
			self._second    = sDT[5]
			self._Normalize()

			self.tzofs = tzofs if ( tzofs != False ) else self.TZOFS
			self.dst   = self.IsDST()
			if( not isGMT ):
				self.second -= self.tzofs

			return True

		self._year   = 1970
		self._month  = 1
		self._day    = 1
		self._hour   = 0
		self._minute = 0
		self._second = 0

		# NOT SET
		if( not sDT ):
			sDT = math.ceil( self.GetSystemTime() )
			isGMT = True

		# A STRING
		elif( not sDT.isnumeric() ):
			parseDTTM = self.Parse( sDT, isGMT, tzofs )
			if( parseDTTM ):
				self.SetDate( parseDTTM, isGMT, tzofs )
				return True

			return False

		# IF BEFORE EPOC
		if( sDT < 0 ):
			# WHILE NEG., SUB YEARS AND ADD TO DATETIME A YR IN SECONDS
			while( sDT < 0 ):
				self._year -= 1
				sDT += ( ( self.cDaysYear + self.IsLeapYear() ) * self.cSecondsDay )

		# YEAR
		while( True ):
			# SECONDS IN THE YEAR
			secondsYear = ( ( self.cDaysYear + self.IsLeapYear() ) * self.cSecondsDay )

			# IF REMOVED ALL THE YEARS FROM THE TIME, QUIT
			if( sDT < secondsYear ):
				break

			# SUBSTRACT SECONDS FROM DATETIME
			sDT -= secondsYear
			self._year += 1

		# MONTH
		while( True ):

			# GET COUNT ON DAYS IN MONTH
			days = ( self._nDaysMonth[self.month] + ( 1 if self.month == 2 and self.IsLeapYear() else 0 ) )

			# GET TOTAL SECONDS IN MONTH
			sMonth = days * self.cSecondsDay

			# IF NO MORE MONTHS IN DATETIME, QUIT
			if( sDT < sMonth ):
				break

			# REMOVE MONTH FROM DATETIME AND ADD A MONTH
			sDT -= sMonth
			self._month += 1

		# DAY
		self._day = int( sDT // self.cSecondsDay ) + 1
		sDT = int( sDT % self.cSecondsDay )

		# HOUR
		self._hour = int( sDT // self.cSecondsHour )
		sDT = int( sDT % self.cSecondsHour )

		# MINUTE
		self._minute = int( sDT // self.cSecondsMinute )
		sDT = int( sDT % self.cSecondsMinute )

		# SECOND
		self._second = int( sDT )

		self._Normalize()

		self.tzofs = tzofs if tzofs != False else self.TZOFS
		self.dst   = self.IsDST()

		# adjust to GMT
		if( not isGMT ):
			self.second -= self.tzofs

	def Duration( self, obj1, obj2, bWantString = False ):
		"""
		Duration takes two objects and returns either a string or array

		:param object obj1
		:param object obj2
		:param boolean bWantString
		:return string|array|false
		"""

		data = []

		obj1 = DTTM( obj1, False )
		obj2 = DTTM( obj2, False )

		diff = abs( obj1.GetUnixTime() - obj2.GetUnixTime() )

		if( diff ):
			data.append( int( diff / self.cSecondsDay ) )
			diff %= self.cSecondsDay

			data.append( int( diff / self.cSecondsHour ) )
			diff %= self.cSecondsHour

			data.append( int( diff / self.cSecondsMinute ) )
			diff %= self.cSecondsMinute

			data.append( diff )

			return "%d %02d:%02d:%02d" % ( data[0], data[1], data[2], data[3] ) if bWantString else data

		return False

	def GetBias( self, tz, isDst = False ):
		"""
		Takes the letters for timezone offset and returns the seconds of offset

		PASS IN 'CST', WILL RETURN -6*3600
		IF CAN'T FIND, RETURNS FALSE

		:param string tz CST,PDT
		:param boolean isDst If observing DST or not
		:return integer|False
		"""

		tz = tz.lower()

		for ofs in self._szTimeZone:
			if( tz in self._szTimeZone[ofs] ):
				if( isDst ):
					ofs += 3600

				return ofs

		return False

	def __str__( self ):
		"""
		IF TRY TO PRINT OBJECT, WILL PRINT STRING

		Magic function to return a print request of object as a string
		:example print dttm prints "2011-12-12 01:02:03"
		"""

		return self.GetString( self.FMT_DTTM )

	def GetMonthName( self, bLong = False ):
		"""
		GetMonthName returns the name of the month either abbr. or full

		:param boolean bLong
		:return string
		"""

		sMonthName = self._szLongMonths[self.month - 1] if bLong else self._szShortMonths[self.month - 1]
		return sMonthName

	def GetWeekdayName( self, bLong = False ):
		"""
		GetWeekdayName returns the name of the day either abbr. or full

		:param boolean bLong
		:return string
		"""

		sWeekdayName = self._szLongDays[self.DayOfWeek()] if bLong else self._szShortDays[self.DayOfWeek()]
		return sWeekdayName

	def IsLeapYear( self, nYear = 0 ):
		"""
		IsLeapYear tells you if the current year is a leap year or not

		:param integer nYear optional, defaults to current year
		:return int 0/1
		"""

		if( nYear == 0 ):
			nYear = self.year

		return int( ( ( nYear & 3 ) == 0 and ( ( ( nYear % 400 ) == 0 ) ^ ( ( nYear % 100 ) != 0 ) ) ) )

	def IsDST( self ):
		"""
		IsDST returns if the current object should be observing dst

		IsDST returns whether or not the date is in daylight saving time
		:return boolean
		"""
		dt = datetime.datetime( self.year, self.month, self.day, self.hour, self.minute, self.second, 0, datetime.timezone.utc )
		return dt.dst()

	def IsWeekDay( self ):
		"""
		Returns true or false if DayOfWeek is Mon-Fri

		:return boolean
		"""

		return bool( self.DayOfWeek( 1 ) < 6 )

	"""
	 " SetTZOfs handles a variety of values passed in and
	 " tries to determine the TZOffset and sets it.
	 " :param mixed value
	 """
	def SetTZOfs( self, tzofs = False ):
		if( tzofs == False ):
			return False

		# FIRST: PARSE VALUE, DETERMINE IF ( 21600, CST, +0000, +00:00 ) SECS, TZ, HOURS, HH:MM

		# -06:00
		if( tzofs.contains( ':' ) ):
			sign = value[0]
			hrs  = value[1,3]
			min  = value[4,6]
			ofs  = hrs * self.cSecondsHour + min * self.cSecondsMinute
			self.tzofs = sign + ofs
			return

		for ofs in self._szTimeZone:
			letters = self._szTimeZone[ofs]

#			# CST
#			if letters.contain(
#			if( in_array( value, letters ) )
#			{
#				self.tzofs = ofs
#				return
#			}
#
#			# 21600
#			if( tm_tzofs == ofs )
#			{
#				self.tzofs = tm_tzofs
#				return
#			}
#		}

	def _Normalize( self ):
		"""
		Takes and makes sure the dttm is proper.

		Normalize makes sure you do not have hours set to 28 or
		minutes set to 300. Advances the date to the correct date time.
		"""

		while( self.second < 0 ):
			self._second    += self.cSecondsMinute
			self._minute    -= 1

		while( self.second >= self.cSecondsMinute ):
			self._second    -= self.cSecondsMinute
			self._minute    += 1

		while( self.minute < 0 ):
			self._minute    += self.cMinutesHour
			self._hour      -= 1

		while( self.minute >= self.cMinutesHour ):
			self._minute    -= self.cMinutesHour
			self._hour      += 1

		while( self.hour < 0 ):
			self._hour      += self.cHoursDay
			self._day       -= 1

		while( self.hour >= self.cHoursDay ):
			self._hour      -= self.cHoursDay
			self._day       += 1

		# ADJUST YEARS LESS THAN 100
		if( self.year < 38 ):
			self._year += 2000
		elif( self.year < 100 ):
			self._year += 1900

		while( True ):
			while( self.month < 1 ):
				self._month += self.cMonthsYear
				self._year  -= 1

			while( self.month > self.cMonthsYear ):
				self._month -= self.cMonthsYear
				self._year  += 1

			nDays = ( self._nDaysMonth[self.month] + ( 1 if ( self.month == 2 and self.IsLeapYear() ) else 0 ) )

			if( self.day < 1 ):
				self._month -= 1

				if( self.month < 1 ):
					self._month += self.cMonthsYear
					self._year  -= 1

				nDays = self._nDaysMonth[self.month] + ( 1 if (self.month == 2 and self.IsLeapYear() ) else 0 )

				self._day += nDays
			elif( self.day > nDays ):
				self._day   -= nDays
				self._month += 1
			else:
				break

		self.dst = self.IsDST()

	def DayOfWeek( self, bISO = False ):
		"""
		Returns which day of the week it is
		"""

		# force to be 0 || 1
		bISO = 1 if bISO else 0

		return( ( ( self.GetLongDate() + 1 - bISO ) % self.cDaysWeek ) + bISO )

	def DayOfYear( self ):
		"""
		Returns the day number of the year

		:return integer
		"""

		dayNumber = 0

		for nMonth in range( 12 ):
			if( nMonth == self.month ):
				break
			dayNumber += self._nDaysMonth[nMonth]

		dayNumber += self.day

		if( self.month > 2 and self.IsLeapYear() ):
			dayNumber += 1

		return dayNumber

	"""
	 " ISOFirstDayOfYear tells you the first day of the year according to ISO standards
	 "
	 " :param integer year
	 " :return object
	 """
	def _ISOFirstDayOfYear( self, year ):
		oFrst = self( "{year}-01-04 00:00:00 GMT" )
		oFrst.day -= ( oFrst.DayOfWeek( True ) - 1 )
		return oFrst

	"""
	 " ISOYear returns which year the day belongs to according to ISO standards
	 "
	 " :return integer
	 """
	def ISOYear( self ):
		year = self.year

		# CHECK JAN AND DEC FOR POSSIBLE VALUES
		if( self.month == 1 or self.month == 12 ):
			# GET THE ISO WEEK OF THE YEAR
			thisWOY = self.WeekOfYear( True )

			# IF WEEK 1 AND IN DEC THEN USE NEXT YEAR
			if( thisWOY == 1 and self.month == 12 ):
				year += 1

			# if last week(s) and in Jan then use previous year
			if( thisWOY >= 52 and self.month == 1 ):
				year -= 1

		return year

	"""
	 " WeekOfYear returns the week of the year
	 "
	 " The function will find either ISO Standard week or just count from January 1 week
	 "
	 " :param boolean ISO
	 " :return integer
	 """
	def WeekOfYear( self, ISO = False ):
		# THE FIRST OF THE YEAR
		thisFDOY = self( "{self.year}-01-01 00:00:00 GMT" )

		# WEEK STARTS WITH SUNDAY 0 BASED
		if( not ISO ):
			thisFDOY.day -= thisFDOY.DayOfWeek( False )
		# WEEK STARTS WITH MONDAY 1 BASED
		else:
			thisFDOY = self._ISOFirstDayOfYear( self.year )
			nextFDOY = self._ISOFirstDayOfYear( self.year + 1 )

			if( thisFDOY.GetLongDate() > self.GetLongDate() ):
				thisFDOY = self._ISOFirstDayOfYear( self.year - 1 )

			if( nextFDOY.GetLongDate() <= self.GetLongDate() ):
				thisFDOY = nextFDOY

		# DIFFERENCE B/T BOTH DATES
		weekofyear = int( ( self.GetLongDate() - thisFDOY.GetLongDate() ) / 7 ) + 1

		return weekofyear

	"""
	 " GetLongDate returns how many days it has been since 0
	 "
	 " :return integer
	 """
	def GetLongDate( self ):
		totalDays = 0
		year = 1

		# ADD 400 YEAR PERIODS
		while( ( year + 400 ) < self.year ):
			totalDays += self.cDays400Years
			year += 400

		# ADD 100 YEAR PERIODS
		while( ( year + 100 ) < self.year ):
			totalDays += self.cDays100Years
			year += 100

		# ADD 4 YEAR LEAP PERIODS
		while( ( year + 4 ) < self.year ):
			totalDays += self.cDays4Years
			year += 4

		# ADD YEARS
		while( year < self.year ):
			totalDays += ( self.cDaysYear + ( 1 if self.IsLeapYear( year ) else 0 ) )
			year += 1

		# ADD NUMBER OF DAYS
		totalDays += ( self.DayOfYear() - 1 )

		# RETURN DAYS
		return totalDays

	"""
	 " GetLongTime returns an integer of how many seconds it has been since mid-night
	 "
	 " @return integer
	 """
	def GetLongTime( self ):
		totalSecs = 0

		# HOURS
		totalSecs += ( self.hour * self.cSecondsHour )

		# MINUTES
		totalSecs += ( self.minute * self.cSecondsMinute )

		# SECONDS
		totalSecs += self.second

		# RETURN RESULT
		return totalSecs

	def GetSystemTime( self ):
		import time
		return time.time()

	"""
	 " GetUnixTime returns how many seconds it has been since the Unix Epoch
	 "
	 " How many seconds has it been since Jan. 1, 1970. If the date
	 " is before that, it will return negative number.
	 " :return integer
	 """
	def GetUnixTime( self ):
		totalDays = 0
		totalSeconds = 0

		totalSeconds = ( self.GetLongDate() - self.cDaysTo1970 ) * self.cSecondsDay

		if( self.year < 1970 ):
			totalSeconds -= ( self.cSecondsDay - self.GetLongTime() ) - self.cSecondsDay
		else:
			totalSeconds += self.GetLongTime()

		if( self.tzofs ):
			totalSeconds -= self.tzofs

		return totalSeconds

	"""
	 " Returns how many days it has been since 12/30/1899 00:00:00
	 "
	 " The integer part is days the decimal part is the time.
	 " :return float
	 """
	def GetOleDateTime( self ):
		lDays   = self.GetLongDate()
		lSecs   = self.GetLongTime()
		dResult = ( lDays - 693593 ) # 12/30/1899 00:00:00

		# For days below the epoch the time is subtracted ???
		if( dResult >= 0.0 ):
			dResult += ( lSecs / self.cSecondsDay )
		else:
			dResult -= ( lSecs / self.cSecondsDay )

		return dResult

	"""
	 " GetLocalTime returns a hash of the date object
	 "
	 " Returns a hash of the dttm object with the year
	 " being from 1900 and the month is 0 based.
	 " @return hash
	 """
	def GetLocalTime( self ):
		return {
			'tm_year'   : self.year - 1900,
			'tm_mon'    : self.month - 1,
			'tm_mday'   : self.day,
			'tm_hour'   : self.hour,
			'tm_min'    : self.minute,
			'tm_sec'    : self.second,
			'tm_wday'   : self.DayOfWeek(),
			'tm_yday'   : self.DayOfYear() }

	"""
	 " GetDateTime returns either an array or a string of the current date time
	 "
	 " :param boolean $bWantArray
	 " :return array|string
	 """
	def GetDateTime( self, bWantArray = False ):
		Time = [ self.year, self.month, self.day, self.hour, self.minute, self.second ]

		return Time if bWantArray else "%04d-%02d-%02d %02d:%02d:%02d" % ( Time )


	"""
	 " GetTimeZone returns the current time zone the dttm object is in
	 "
	 " :param boolean $label If to return the timezone label. CDT, PST, etc.
	 " :param boolean $separate If to return the timezone offset with a seperator. 06:00 or 0600
	 " :return string
	 """
	def GetTimeZone( self, label = True, separate = False ):
		ofs  = ''
		sign = '+' if self.tzofs >= 0 else '-'
		secs = abs( self.tzofs )
		lbl  = ''

		# GMT, UTC, CST, EST
		if( label ):
			if( self.IsDST() ):
				if 1 in self._szTimeZone[( self.tzofs - self.cSecondsHour )]:
					lbl = self._szTimeZone[( self.tzofs - self.cSecondsHour )][1]
				else:
					lbl = self._szTimeZone[self.tzofs][0]
			else:
				lbl = self._szTimeZone[self.tzofs][0]

			return lbl.upper()
		# HH:SS
		else:
			hrs = secs // 3600
			sec = secs % 3600

			if( separate ):
				ofs = "%02d" % ( hrs ) + ":" + "%02d" % ( sec )
			else:
				sec = ( sec // 60 ) * 100
				ofs = "%02d" % ( hrs ) + "%02d" % ( sec )

		return sign + ofs

	"""
	 " GetDaysInMonth returns number of days in the month
	 "
	 " :param integer nMonth Optional.
	 " :return integer
	 """
	def GetDaysInMonth( self, nMonth = 0 ):
		total_days = 0

		if( nMonth == 0 ):
			nMonth = self.month

		total_days = self._nDaysMonth[nMonth]

		if( nMonth == 2 and self.IsLeapYear() ):
			total_days += 1

		return total_days

	"""
	 " Returns the language end of the dates
	 "
	 " :return string
	 """
	def GetDaySuffix( self ):
		suffixes = ( 'th', 'st', 'nd', 'rd' )
		s        = suffixes[0]
		hund     = self.day % 100
		ten      = self.day % 10

		if( not( hund >= 11 and hund <=13 ) and ten < 4 ):
			s = suffixes[ten]

		return s

	"""
	 " Takes a letter and returns that part of the date object
	 "
	 " Works on the same letters as defined in php's date function.
	 " :param string $part
	 " :return string
	 """
	def GetPart( self, sPart ):

		##### Year #####

		# Whether it's a leap year
		if( sPart == 'L' ):
			return self.IsLeapYear()

		# A two digit representation of a year
		elif( sPart == 'y' ):
			return "%02d" % ( self.year % 100 )

		# A full numeric representation of a year, 4 digits
		elif( sPart == 'Y' ):
			return "%02d" % ( self.year )

		# ISO Year
		elif( sPart == 'o' ):
			return self.ISOYear()

		##### Month #####

		# A full textual representation of a month, such as January or March
		if( sPart == 'F' ):
			return self._szLongMonths[self.month - 1]

		# A short textual representation of a month, three letters
		elif( sPart == 'M' ):
			return self._szShortMonths[self.month - 1]

		# Numeric representation of a month, with leading zeros
		elif( sPart == 'm' ):
			return "%02d" % ( self.month )

		# Numeric representation of a month, without leading zeros
		elif( sPart == 'n' ):
			return self.month

		# Number of days in the given month
		elif( sPart == 't' ):
			return self.GetDaysInMonth()

		##### Week #####

		# ISO
		if( sPart == 'W' ):
			return "%02d" % ( self.WeekOfYear( True ) )

		# Calendar
		elif( sPart == 'C' ):
			return "%02d" % ( self.WeekOfYear( False ) )

		##### Day #####

		# Day of the month, 2 digits with leading zeros
		if( sPart == 'd' ):
			return "%02d" % ( self.day )

		# A textual representation of a day, three letters
		elif( sPart == 'D' ):
			return self._szShortDays[self.DayOfWeek()]

		# Day of the month without leading zeros
		elif( sPart == 'j' ):
			return self.day

		# A full textual representation of the day of the week
		elif( sPart == 'l' ):
			return self._szLongDays[self.DayOfWeek()]

		# ISO-8601 numeric representation of the day of the week 1 - Mon, 7 - Sun
		elif( sPart == 'N' ):
			return self.DayOfWeek( True )

		# Numeric representation of the day of the week 0 - Sun, 6 - Sat
		elif( sPart == 'w' ):
			return self.DayOfWeek()

		# The day of the year ( starting from 0 - 365 )
		elif( sPart == 'z' ):
			return self.DayOfYear() - 1

		elif( sPart == 'S' ):
			return self.GetDaySuffix()

		##### Time #####

		# Lowercase Ante meridiem and Post meridiem
		if( sPart == 'a' ):
			return 'am' if self.hour < 12 else 'pm'

		# Uppercase Ante meridiem and Post meridiem
		elif( sPart == 'A' ):
			return 'AM' if self.hour < 12 else 'PM'

		# Swatch Internet time
#		elif( sPart == 'B' ):
#			return( sprintf( "%03d", intval( ( ( self.GetLongTime() - self.tzofs + self.cSecondsHour ) % self.cSecondsDay / self.cSecondsDay ) * 1000 ) ) )

		# 24-hour format of an hour without leading zeros
		elif( sPart == 'G' ):
			return int( self.hour )

		# 12-hour format of an hour without leading zeros
		elif( sPart == 'g' ):
			return ( self.hour + 11 ) % 12 + 1

		# 12-hour format of an hour with leading zeros
		elif( sPart == 'h' ):
			return "%02d" % ( self.GetPart( 'g' ) )

		# 24-hour format of an hour with leading zeros
		elif( sPart == 'H' ):
			return "%02d" % ( self.hour )

		# Minutes with leading zeros
		elif( sPart == 'i' ):
			return "%02d" % ( self.minute )

		# Seconds, with leading zeros
		elif( sPart == 's' ):
			return "%02d" % ( self.second )

		##### Timezones #####

		# Whether or not the date is in daylight saving time
		if( sPart == 'I' ):
			return self.IsDST()

		# Timezone abbreviation
		elif( sPart == 'T' ):
			return self.GetTimeZone()

		# Difference to Greenwich time (GMT) with colon between hours and minutes
		elif( sPart == 'P' ):
			return self.GetTimeZone( False, True )

		# Difference to Greenwich time (GMT) in hours
		elif( sPart == 'O' ):
			return self.GetTimeZone( False )

		# Timezone offset in seconds.
		elif( sPart == 'Z' ):
			return self.tzofs

		# Timezone identifier
#		elif( sPart == 'e' ):
#			return( date( 'e', self.GetUnixTime() ) )

		##### PRE FORMATTED #####

		# ISO-8601
		if( sPart == 'c' ):
			return self.GetString( "Y-m-d\\TH:i:sP" )

		# RFC2822
#		elif( sPart == 'r' ):
#			return self.GetString( DATE_RFC2822 )

		# Timestamp
		elif( sPart == 'U' ):
			return self.GetUnixTime()

		return sPart

	def GetString( self, sFormat = False, useTZ = True ):
		"""
			Takes a date format string and returns the dttm part

			:param string sFormat
			:param boolean useTZ
			:return string
		"""

		if( sFormat == False ):
			sFormat = self.FMT_DTTM

		result = ''
		tm_tzofs = self.tzofs

		print( "\n1: " + str( self.hour ) + " " + str( self.minute ) + " " + str( self.second ) )

		if( useTZ ):
			self.second += int( self.tzofs )
		else:
			self.tzofs = 0

		print( "\n2: " + str( self.hour ) + " " + str( self.minute ) + " " + str( self.second ) )

		for i in range( len( sFormat ) ):
			result += sFormat[i] if sFormat[i] == '\\' else self.GetPart( sFormat[i] )

		if( useTZ ):
			self.second -= self.tzofs
		else:
			self.tzofs = tm_tzofs

		return result

	@staticmethod
	def Parse( sz, isGMT = False, tzofs = False ):

		"""
			Static Method
				Parse is a static function designed to handle a variety of date strings

				:param string sz
				:param boolean isGMT
				:param boolean tzofs
				:return object|None
		 """

		rc = [ 1900, 1, 1, 0, 0, 0 ]

		months = '|'.join( DTTM._szShortMonths )

		# pattern, year, month, day
		tests = [
			# yyyy/mm/dd
			[ "^(\\d{4})[^\\w:](\\d{1,2})[^\\w:](\\d{1,2})", 0, 1, 2 ],

			# mm/dd/yy[yy]
			[ "^(\\d{1,2})[^\\w:](\\d{1,2})[^\\w:](\\d{1,4})", 2, 0, 1 ],

			# dd mon year
			[ "^(\\d{1,2})[^\\w:]+(" + months + ")\\w*[^\\w:]+(\\d{2,4})", 2, 1, 0 ],

			# mon dd year
			[ "^(" + months + ")\\w*[^\\w:]+(\\d{1,2})[^\\w:]+(\\d{2,4})", 2, 0, 1 ],

			# month year
			[ "^(" + months + ")\\w*[^\\w:]+(\\d{2,4})", 1, 0, None ],
		]

		bOK = False

		for t in tests:
			RegExp = re.compile( t[0], re.IGNORECASE )
			m = RegExp.search( sz )
			if( m == None ):
				continue

			Matches = m.groups()

			if( Matches ):
				for r in range( 1, 4 ):
					szdt = Matches[int(t[r])]

					if( not szdt ):
						szdt = 1

					# ascii month
					if( r == 1 and not szdt.isnumeric() ):
						for m in range( 0, 12 ):
							if( szdt.upper() == self._szShortMonths[m].upper() ):
								rc[r] = m + 1
								break
					else:
						# remove leading zeros and assign
						rc[r-1] = int( szdt ) # .lstrip( '0' )

				if( rc[0] < 100 ):
					oDT = DTTM()
					rc[0] += int( oDT.GetString( 'Y' ) / 100 ) * 100

					if( ( oDT.GetString('Y') - rc[0] ) > 20 ):
						rc[0] += 100

					if( rc[0] - 20 > oDT.GetString('Y') ):
						rc[0] -= 100

				# validation
				bOK = ( ( rc[1] >= 1 and rc[1] <= 12 ) and ( rc[2] >= 1 and rc[2] <= 31 ) )
				break

		# 10:21[:21][[A|M]P] [+-xx:xx]
		# 1-3 h:m:s
		# 4 am|pm
		# 5 tz
		RegExp = re.compile( "(\\d{1,2}):(\\d{1,2}):?(\\d{1,2})?\\.?\\d*\\s*([AP]M)?\s*([+-]\\d{1,2}:?\\d{1,2})?$", re.IGNORECASE )
		m = RegExp.search( sz )
		if( m != None ):
			tm = m.groups()

			if( tm ):
				# remove leading zeros and assign
				for r in range( 3 ):
					if( tm[r] ):
						rc[r+3] = int( tm[r] )

				if( tm[3] and tm[3].upper() == 'PM' and rc[2] < 12 ):
					rc[2] += 12

				if( tzofs == False and tm[4] ):
					tzofs = int( tm[4].replace( ":", "" ) ) * 36

				# validation
				bOK = True

		if( bOK ):
			return DTTM( rc, isGMT, tzofs )


	"""
	 " Fetch works off date set on object and parses about any English textual datetime description
	 "
	 " :param string $sTarget +1 week, next week, +5 days, etc
	 " :return object|false
	 """
#	def Fetch( sTarget ):
#		static $szLongDays     = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' )
#
#		static $szLongMonths   = array( 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december' )
#
#		$modify = array()
#
#		static $txtmods = array(
#					'yesterday' => 'yesterday',
#					'tomorrow'  => 'tomorrow',
#					'today'     => 'today',
#					'previous'  => -1,
#					'first'     => 'first',
#					'this'      => 1,
#					'next'      => 1,
#					'second'    => 'second',
#					'third'     => 'third',
#					'fourth'    => 'fourth',
#					'last'      => 'last',
#					)
#
#		# figure out what modifier they want
#		$string = '/^(yesterday|tomorrow|today|previous|this|next|first|second|third|fourth|last|\+|-)?(\d+)?/i'
#		preg_match( $string, $sTarget, $parse )
#
#		if( $parse )
#		{
#			# + 2
#			if( isset( $parse[2] ) && $parse[2] != '' )
#			{
#				$modify[0] = intval( ( ( $parse[1] == "" ) ? "+" : $parse[1] ) . $parse[2] )
#			}
#			# next, first, etc
#			else
#			{
#				if( $parse[1] != '' )
#					$modify[0] = $txtmods[strtolower( $parse[1] )]
#				else
#					return( false )
#			}
#		}
#		else
#			return( false )
#
#		# HANDLE SINGLE WORD
#		if( in_array( $modify[0], array( 'yesterday', 'tomorrow', 'today' ) ) )
#		{
#			$modify[0] = strtolower( $modify[0] )
#
#			$dt = new DTTM( $this )
#			$dt->SetTime()
#
#			if( $modify[0] == 'yesterday' )
#			{
#				$dt->tm_day--
#			}
#			else if( $modify[0] == 'tomorrow' )
#			{
#				$dt->tm_day++
#			}
#
#			$dt->_Normalize()
#
#			return( $dt )
#		}
#
#		# now find what time they want to modify
#		$string = '/(second|minute|hour|day|week|month|year|' . implode( "|", $szLongDays ) . '|' . implode( "|", $szLongMonths ) . ')s?$/i'
#		preg_match( $string, $sTarget, $parse )
#
#		if( $parse )
#			$modify[1] = strtolower( $parse[1] )
#		else
#			return( false )
#
#		#### IF HERE, WE PARSED STRING, DO SOMETHING!!
#
#		$dt = new DTTM( $this )
#
#		#FIRST - check to see if passing last or first
#		if( in_array( $modify[0], array( 'first', 'second', 'third', 'fourth' ) ) ) # first monday
#		{
#			# day of week looking for
#			$dow = array_search( strtolower( $modify[1] ), $szLongDays )
#
#			if( is_int( $dow ) )
#			{
#				$dt->tm_day = 1
#
#				while( $dt->DayOfWeek( false ) != $dow )
#					$dt->day += 1
#
#				if( $modify[0] != 'first' )
#				{
#					$factors = array(
#							'second' => 1,
#							'third' => 2,
#							'fourth' => 3 )
#
#					$dt->day += ( $factors[$modify[0]] * 7 )
#				}
#			}
#		}
#		else if( $modify[0] == 'last' ) # last friday
#		{
#			# day of week looking for
#			$dow = array_search( strtolower( $modify[1] ), $szLongDays )
#
#			if( is_integer( $dow ) )
#			{
#				$dt->tm_day = self._nDaysMonth[$dt->tm_month]
#
#				if( $dt->tm_month == 2 )
#					$dt->tm_day += $dt->IsLeapYear()
#
#				while( $dt->DayOfWeek( false ) != $dow )
#					$dt->tm_day -= 1
#			}
#			else if( $modify[1] == 'day' )
#			{
#				$dt->tm_day = self._nDaysMonth[$dt->tm_month]
#
#				if( $dt->tm_month == 2 )
#					$dt->tm_day += $dt->IsLeapYear()
#			}
#			else if( $modify[1] == 'month' )
#			{
#				$dt->month--
#			}
#		}
#		## From here, $modify[0] should be an integer
#		else if( in_array( $modify[1], array( 'second', 'minute', 'hour', 'day', 'month', 'year' ) ) )
#		{
#			$name = 'tm_' . $modify[1]
#			$dt->$name += $modify[0]
#		}
#		else if( $modify[1] == 'week' )
#		{
#			$dt->tm_day += ( 7 * $modify[0] )
#		}
#		else if( in_array( strtolower( $modify[1] ), $szLongDays ) )
#		{
#			$mod_dow = array_search( strtolower( $modify[1] ), $szLongDays )
#
#			if( $modify[0] > 0 )
#			{
#				# CATCH EX. DATE = SUNDAY, WANT NEXT SUNDAY - ALLOW TO GO AHEAD
#				if( $dt->DayOfWeek( false ) == $mod_dow )
#					$dt->day += 1
#
#				while( $dt->DayOfWeek( false ) != $mod_dow )
#					$dt->tm_day += 1
#
#				if( $modify[0] > 1 )
#					$dt->tm_day += ( 7 * ( $modify[0] - 1 ) )
#			}
#			else
#			{
#				# CATCH EX. DATE = SUNDAY, WANT PREVIOUS SUNDAY - ALLOW TO GO BACK A WEEK
#				if( $dt->DayOfWeek( false ) == $mod_dow )
#					$dt->day -= 1
#
#				while( $dt->DayOfWeek( false ) != $mod_dow )
#					$dt->tm_day -= 1
#
#				if( $modify[0] < -1 )
#					$dt->tm_day -= ( 7 * ( abs( $modify[0] + 1 ) ) )
#			}
#		}
#		else if( in_array( strtolower( $modify[1] ), $szLongMonths ) ) #next January
#		{
#			$mod_moy = array_search( strtolower( $modify[1] ), $szLongMonths ) + 1
#
#			if( $modify[0] > 0 )
#				$dt->tm_year += $modify[0]
#			else
#				$dt->tm_year -= $modify[0]
#
#			$dt->tm_month = $mod_moy
#		}
#
#		$dt->_Normalize()
#
#		return( $dt )

	"""
	 " Format is a static function to parse a dttm string and format it properly
	 "
	 " If no date is passed in returns false.
	 " 2014-10-31 , 10-31-2014 Oct. 31, 2014
	 " :example DTTM::Format( '2014-01-01 12:13:01', $ini['APP']['FMT_DT'], false, true )
	 " :param string $sDate
	 " :param string $sFormat
	 " :param boolean $isGMT
	 " :return boolean $useTZ
	 """
	def Format( sDate = '', sFormat = '', isGMT = True, useTZ = True ):
		if( not len( sFormat ) ):
			sFormat = self.FMT_DTTM

		dt = self( sDate, isGMT )
		if( sDate and dt ):
			return dt.GetString( sFormat, useTZ )

		return False
