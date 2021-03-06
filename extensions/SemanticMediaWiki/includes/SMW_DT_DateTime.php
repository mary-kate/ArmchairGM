<?php
/**
 * Support functions for evaluating datatypes that accept dates.
 * Support both XSD dates and seconds before/since 1970
 * See http://www.w3.org/TR/xmlschema-2/#dateTime
 * The implementation is dependent on PHP's strtotime(),
 * which in turn uses GNU get_date().
 *
 * @author skierpage
 */

/******* Datatype handler classes ********/

class SMWDateTimeTypeHandler implements SMWTypeHandler {


	/** @public */

	function getID() {
		return 'datetime';
	}

	function getXSDType() {
		return 'http://www.w3.org/2001/XMLSchema#dateTime';
	}

	function getUnits() { //no units for string
		//return array('STDUNIT'=>'ISO8601', 'ALLUNITS'=> array('ISO8601', 'seconds since 1970') ); //->this is not what we want -- mak
		return array('STDUNIT'=>false, 'ALLUNITS'=>array());
	}


    /**
	 * This method transforms the user-provided value of an
	 * attribute into several output strings (one for XML,
	 * one for printout, etc.) and reports parsing errors if
	 * the value is not valid for the given data type.
	 *
	 * @access public
	 */
	function processValue($v,&$datavalue) {
		// For a DateTime, "units" is really a format from an inline query
		// rather than the units of a float. 
		$desiredUnits = $datavalue->getDesiredUnits();
		$str_val = trim($v);
		$time = strtotime($str_val);
		if ($time == -1 || $time === false) {
			$datavalue->setError('<span class="smwwarning">' . wfMsgForContent('smw_nodatetime',$v) . '</span>');
			return;
		}

		// strtotime accepts non-ISO8601 times like 02/01/70,
		// so reformat back to ISO8601. Unfortunatelly, ISO in
		// general is not compatible with XSD; but it should work
		// for the restricted interval we currently support.
		$date_part = strftime("%Y-%m-%d", $time);
		$time_part = strftime("%H:%M:%S", $time);
		$str_val = $date_part . 'T' .$time_part; // always show time in XSD.  TODO: Should I use PHP date('c') format for ISO8601?
		$datavalue->setProcessedValues($v, $str_val, $time);

		// Determine the user-visible string.		
		if (count($desiredUnits) ==0) {
			// The default user-visible string shows date...
			$user_val = $date_part;
			// ... followed by a space and the time if there is a significant time component.
			// TODO: should I indicate the timezone in user-visible string?
			if ( abs($time - strtotime($date_part)) > 0.5) {
				$user_val .= ' ' . $time_part;
			}
			$datavalue->setPrintoutString($user_val);
		} else {
			// Print the date in all wanted formats (even if some of them would be equivalent -- we obey the user's wish)
			foreach ($desiredUnits as $wantedFormat) {
				$datavalue->setPrintoutString(strftime($wantedFormat, $time));
			}
		}

		//smwfNumberFormat($time) . ' seconds since 1970' ;
		// do not show the seconds since 1970; showing a date in multiple calendar systems could be a future output enhancement (Roman, Gregorian, whatever calendar), if the date is "historical" enough

		$datavalue->addQuicksearchLink();
		$datavalue->addServiceLinks($str_val); //possibly provide single values (year, month, ...) in the future
		return;
	}

	/**
	 * This method parses the value in the XSD form that was
	 * generated by parsing some user input. It is needed since
	 * the XSD form must be compatible to XML, and thus does not
	 * respect the internationalization settings. E.g. the German
	 * input value "1,234" is translated into XSD "1.234" which,
	 * if reparsed as a user input would be misinterpreted as 1234.
	 *
	 * @public
	 */
	function processXSDValue($value,$unit,&$datavalue) {
		//just insert the local decimal separator
		//(kilo separators do not occur in XSD)
		return $this->processValue(
		        str_replace('.',wfMsgForContent('smw_decseparator'),$value) . $unit, $datavalue);
	}

	/** @private */

	/**
	 * DateTime is stored as a number (seconds since 1970), but its XSD 
	 * format ISO8601 is not numeric.  Hmmm.
	 */
	function isNumeric() {
		return TRUE;
	}
} //SMWDateTimeTypeHandler

//register type handler:
//SMWTypeHandlerFactory::registerTypeHandler($smwgContLang->getDatatypeLabel('smw_datetime'),
//                       new SMWDateTimeTypeHandler());

?>
