<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Wiki engine graphics extension
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Martijn Beulens, Michele Silletti - original MyGraph class
 * @author Tomasz Klim <tomek@wikia.com> - fixes, output buffering, porting to PHP5+MediaWiki
 * @copyright Copyright (C) 2007 Tomasz Klim, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['other'][] = array(
	'name' => 'WikiGraph',
	'description' => 'universal timeline graph extension',
	'author' => 'Tomasz Klim'
);



/*
	@Script
	//---------------------------------------------------------------------------
	@ScriptLicense: GNU General Public License (GPL)
	@ScriptVersion: 0.3
	@ScriptName: MyGraph Class
	@ScriptDescription: Produces graph pictures
	@ScriptRequirements: PHP4 + GD2 installed
	@ScriptAuthor: Martijn Beulens, Michele Silletti
	@ScriptAuthorWebsite: http://www.vorlox.com

	@Configuration
	//---------------------------------------------------------------------------

		@You can build on optional $cfg array like this
		//-----------------------------------------------------------------------
		$cfg['ImgTitle'] = "";				// Sets the title of the graph (if left empty no title will be displayed)
		$cfg['ImgTitleColor'] = "";			// Sets the color of the title text (default 0000FF)
		$cfg['ImgWidth'] = "";				// Sets the width of the image (default 500)
		$cfg['ImgHeight'] = "";				// Sets the height of the image (default 150)
		$cfg['ImgBackgroundColor'] = "";		// Sets the image background (default FFFFFF)
		$cfg['ImgTextColor'] = "";			// Sets the image text color (default 000000)
		$cfg['ImgPaddingTop'] = "";			// Sets the top padding for the graphbox (default 20)
		$cfg['ImgPaddingLeft'] = "";			// Sets the left padding for the graphbox (default 40)
		$cfg['ImgPaddingBottom'] = "";			// Sets the bottom padding for the graphbox (default 20)
		$cfg['ImgPaddingRight'] = "";			// Sets the right padding for the graphbox (default 40)
		$cfg['BoxBackgroundColor'] = "";		// Sets the background color of the graph box (default FFFFFF)
		$cfg['BoxBorderColor'] = "";			// Sets the border color of the graph box (default 000000)
		$cfg['BoxGridColor'] = "";			// Sets the grid color (default CCCCCC)
		$cfg['BoxTextColor'] = "";			// Sets the color of the text over the indicating box in the graph (default 0000FF)
		$cfg['ColumnColor'] = "";			// Sets the color of the value indication (default 00CC00)
		$cfg['CompareColumnColor'] = "";		// Sets the color of the compare value indication (default FF0066)
		$cfg['LegendaPadding'] = "";			// Sets the padding of the left legenda text (default 30)
		$cfg['UnitOfMeasure'] = "";			// Unit of measure (default "")
		$cfg['ColumnTextTrigger'] = "";			// Toggles Column text on/off values(1/0) (default 1)
		$cfg['BlockTextTrigger'] = "";			// Toggles Block text on/off values(1/0) (default 1)
		$cfg['AvarageTrigger'] = "";			// Toggles avarage line on/off values(1/0) (default 1)
		$cfg['GridTrigger'] = "";			// Toggles grid on/off values(1/0) (default 1)
		$cfg['LegendaTextTrigger'] = "";		// Toggles legenda text on/off values(1/0) (default 1)
		$cfg['TitleTextTrigger'] = "";			// Toggles Title Text on/off values(1/0) (default 1)
		$cfg['tmpDir'] = "";				// Path to writable tmp dir for images (default /tmp/)
		$cfg['wwwDir'] = "";				// Server (mapped) path for images (default /img/)
		$cfg['Parse'] = "";				// 1 return html image - 2 return imagecontent (default 1)
		$cfg['Template'] = "";				// Output tag template

		//V0.2
		$cfg['LegendaBlockTrigger'] = "";		// Toggles Legenda block for compare graph (default 0)
		$cfg['LegendaBlockText'] = "";			// Text for legenda block first DataArray (default "")
		$cfg['CompareLegendaBlockText'] = "";		// Text for legenda block second DataArray (default "")

		//V0.3
		$cfg['BlockTextVert'] = "";			// if set to 1 (true) shows vertical block text, padding correctly the image. //contributed by Michele Silletti
		$cfg['MinColWidth'] = "";			// If set to a number larger then 0. It will automatically calculate the image width.
		$cfg['BenchMark'] = "";				// Sets a benchmark for the graph and the outcome will be displayed in the titletext color in the upper right corner of the image (default 0)

	@Methods
	//---------------------------------------------------------------------------

		@Callable Methods
		//-----------------------------------------------------------------------
		BlockGraph($DataArray,$cfg=array())		// Builds a simpel block graph
		LineGraph($DataArray,$cfg=array());		// Builds a simpel line graph
		PolyGraph($DataArray,$cfg=array());		// Builds a filled polygon graph
		CompareGraph($DataArray,$DataArrayCompare,$cfg=array());	// Builds a double line graph out of two data arrays

		@Internal Methods
		//-----------------------------------------------------------------------
		WikiGraph();					// Default constructor (does nothing)
		CheckConfiguration();				// Checks the full configuration otherwise return error
		ErrorImg();					// Returns the configuration error as a picture
		ExtractArray($Array);				// Builds two seperate arrays from the keys and values of the incomming DataArray
		ExtractCompareArray($Array);			// Builds two seperate arrays from the keys and values of the incomming CompareDataArray
		CreateImg();					// Creates img pointer
		PrintImg();					// Print image
		CreateTitle();					// Creating title
		CreateLegenda();				// Creating left legenda
		CreateAvarage();				// Creating avarage line
		CreateCompareAvarage();				// Creating compare avarage line
		CreateBlockText();				// Creating the block texts
		CreateLine();					// Creating the line
		CreateCompareLine();				// Creating the compare line
		CreatePoly();					// Creating the polygons
		CreateBlock();					// Creating the blocks
		CreateColumnText();				// Creating the colomntexts
		CreateGrid();					// Creating the grid
		CreateBoxBackground();				// Creating the box background
		CreateBoxBorder();				// Creating the BoxBorder
		ConvertHex($hex);				// Converting a hex to r,g,b array
		CreateLegendaBlock();				// Creates a legenda block

	@Usage
	//---------------------------------------------------------------------------

		@Simple Example
		//-----------------------------------------------------------------------

		//Build Data Array
		$DataArray = array
		(
			'Jan'=>'12',
			'Feb'=>'224',
			'Mar'=>'18',
			'Apr'=>'54',
			'May'=>'43',
			'Jun'=>'16',
			'Jul'=>'21',
			'Aug'=>'23',
			'Sep'=>'1',
			'Okt'=>'120',
			'Nov'=>'189',
			'Dec'=>'177'
		);

		//Init graph class
		$MyGraph = new WikiGraph();

		//Call LineGraph Method and send DataArray
		$MyGraph->LineGraph($DataArray);

		@Simple Example with configuration
		//-----------------------------------------------------------------------

		//Configure
		$cfg['ImgTitle'] = "This is MyGraph";		// Sets the title of the graph (if left empty no title will be displayed)
		$cfg['ImgTitleColor'] = "FF0000";		// Sets the color of the title text (default 0000FF)
		$cfg['ImgWidth'] = "700";			// Sets the width of the image (default 500)
		$cfg['ImgHeight'] = "200";			// Sets the height of the image (default 150)
		$cfg['ImgBackgroundColor'] = "#FFFF99";		// Sets the image background (default FFFFFF)

		//Build Data Array
		$DataArrayA = array
		(
			'Jan'=>'12',
			'Feb'=>'224',
			'Mar'=>'18',
			'Apr'=>'54',
			'May'=>'43',
			'Jun'=>'16',
			'Jul'=>'21',
			'Aug'=>'23',
			'Sep'=>'1',
			'Okt'=>'120',
			'Nov'=>'189',
			'Dec'=>'177'
		);

		//Init graph class
		$MyGraph = new WikiGraph();

		//Call LineGraph Method and send DataArray
		$MyGraph->PolyGraph($DataArrayA,$cfg);

*/



/* WikiGraph */
class WikiGraph
{

//-------------------------------------------------------------------------------

	/* Vars & Arrays */
	var $cfg = array();				//Configuration array
	var $IncommingArray = array();			//Incomming array
	var $ColumnArray = array();			//Column array
	var $DataArray = array();			//Data array
	var $CompareDataArray = array();		//CompareData array
	var $IncommingCompareArray = array();		//Incomming CompareData array
	var $LogArray = array();			//Log array
	var $ip = null;					//ImagePointer
	var $ColorArray = array();			//Color Pointers
	var $BoxDimensions = array();			//Box Dimensions
	var $GridDimensions = array();			//Grid Dimensions
	var $BlockDimensions = array();			//Block Dimensions
	var $CompareBlockDimensions = array();		//Compare Block Dimensions
	var $BlockTextDimensions = array();		//Block Text Dimensions
	var $LegendaDimensions = array();		//Legenda Text Dimensions
	var $AvarageDimensions = array();		//Avarage line Dimensions
	var $CompareAvarageDimensions = array();	//Compare Avarage line Dimensions
	var $High = null;				//Highest value in DataArray
	var $CompareHigh = null;			//Highest value in CompareDataArray
	var $Avarage = null;				//Avarage Value in DataArray
	var $CompareAvarage = null;			//Avarage Value in CompareDataArray
	var $BenchStart = null;				//Benchmark
	var $BenchStop = null;				//Benchmark
	var $Buffer = '';				//Output Buffer

//-------------------------------------------------------------------------------

	/* Constructor */
	function WikiGraph()
	{
		/* Do Nothing */

	} //End function WikiGraph()

//-------------------------------------------------------------------------------

	/* Check Configuration */
	function CheckConfiguration()
	{
		//Start Becnhamrk
		$this->BenchStart = $this->getmicrotime();

		/* Var */
		$Check = true;

		/* Check Required Settings otherwise fill with defaults */
		if(empty($this->cfg['ImgTitle'])) { $this->cfg['ImgTitle'] = ""; }
		if(empty($this->cfg['ImgTitleColor'])) { $this->cfg['ImgTitleColor'] = "0000FF"; }
		if(empty($this->cfg['ImgWidth'])) { $this->cfg['ImgWidth'] = "500"; }
		if(empty($this->cfg['ImgHeight'])) { $this->cfg['ImgHeight'] = "150"; }
		if(empty($this->cfg['ImgBackgroundColor'])) { $this->cfg['ImgBackgroundColor'] = "FFFFFF"; }
		if(empty($this->cfg['ImgTextColor'])) { $this->cfg['ImgTextColor'] = "000000"; }
		if(empty($this->cfg['ImgPaddingTop'])) { $this->cfg['ImgPaddingTop'] = "20"; }
		if(empty($this->cfg['ImgPaddingLeft'])) { $this->cfg['ImgPaddingLeft'] = "40"; }
		if(empty($this->cfg['ImgPaddingBottom'])) { $this->cfg['ImgPaddingBottom'] = "20"; }
		if(empty($this->cfg['ImgPaddingRight'])) { $this->cfg['ImgPaddingRight'] = "40"; }
		if(empty($this->cfg['BoxBackgroundColor'])) { $this->cfg['BoxBackgroundColor'] = "FFFFFF"; }
		if(empty($this->cfg['BoxBorderColor'])) { $this->cfg['BoxBorderColor'] = "000000"; }
		if(empty($this->cfg['BoxGridColor'])) { $this->cfg['BoxGridColor'] = "CCCCCC"; }
		if(empty($this->cfg['BoxTextColor'])) { $this->cfg['BoxTextColor'] = "0000FF"; }
		if(empty($this->cfg['ColumnColor'])) { $this->cfg['ColumnColor'] = "00CC00"; }
		if(empty($this->cfg['CompareColumnColor'])) { $this->cfg['CompareColumnColor'] = "FF0066"; }
		if(empty($this->cfg['LegendaPadding'])) { $this->cfg['LegendaPadding'] = "30"; }
		if(empty($this->cfg['UnitOfMeasure'])) { $this->cfg['UnitOfMeasure'] = ""; }
		if(!isset($this->cfg['ColumnTextTrigger'])) { $this->cfg['ColumnTextTrigger'] = "1"; }
		if(!isset($this->cfg['BlockTextTrigger'])) { $this->cfg['BlockTextTrigger'] = "1"; }
		if(!isset($this->cfg['AvarageTrigger'])) { $this->cfg['AvarageTrigger'] = "1"; }
		if(!isset($this->cfg['GridTrigger'])) { $this->cfg['GridTrigger'] = "1"; }
		if(!isset($this->cfg['LegendaTextTrigger'])) { $this->cfg['LegendaTextTrigger'] = "1"; }
		if(!isset($this->cfg['TitleTextTrigger'])) { $this->cfg['TitleTextTrigger'] = "1"; }
		if(empty($this->cfg['tmpDir'])) { $this->cfg['tmpDir'] = "/tmp/"; }
		if(empty($this->cfg['wwwDir'])) { $this->cfg['wwwDir'] = "/img/"; }
		if(empty($this->cfg['AvarageColor'])) { $this->cfg['AvarageColor'] = "FF0000"; }
		if(empty($this->cfg['Parse'])) { $this->cfg['Parse'] = "1"; }
		if(empty($this->cfg['Template'])) { $this->cfg['Template'] = "<img src=\"__URL__\" border=\"0\">"; }

		if(!isset($this->cfg['LegendaBlockTrigger'])) {	$this->cfg['LegendaBlockTrigger'] = "0"; }
		if(empty($this->cfg['LegendaBlockText'])) { $this->cfg['LegendaBlockText'] = ""; }
		if(empty($this->cfg['CompareLegendaBlockText'])) { $this->cfg['CompareLegendaBlockText'] = ""; }

		if(!isset($this->cfg['MinColWidth'])) { $this->cfg['MinColWidth'] = 0; }
		if(!isset($this->cfg['BenchMark'])) { $this->cfg['BenchMark'] = 0; }

		//Check Bottom Padding for legenda compare
		if($this->cfg['LegendaBlockTrigger']==1)
		{
			if($this->cfg['ImgPaddingBottom']<50) { $this->cfg['ImgPaddingBottom'] = "50"; }
		}

		/* Check Incomming Data */
		if(!$this->ExtractArray($this->IncommingArray)) { $Check = false; $this->LogArray[] = "Posted Array is not a valid DataArray!"; }

		/* Check Data array */
		foreach($this->DataArray as $val)
		{
			if(!is_numeric($val)) { $Check = false; $this->LogArray[] = "Not all posted DataArray values are numeric!"; }
		} //End foreach($this->DataArray as $val)

		//Check minimum col width
		if(!empty($this->cfg['MinColWidth']))
		{
			$MinColWidthCalc = count($this->DataArray)*$this->cfg['MinColWidth'];

			//Set new img width
			if($this->cfg['ImgWidth'] < $MinColWidthCalc)
			{
				$this->cfg['ImgWidth'] = $MinColWidthCalc;
			} //End if($MinColWidthCalc < $this->cfg['ImgWidth'])

		} //End if(!empty($this->cfg['MinColWidth']))

		//Check if blocktext must be vertical
		//Nice calculation contributed by Michele Silletti
		if($this->cfg['BlockTextVert']==1)
		{
			$maxheightblocktext=$this->cfg['ImgPaddingBottom'];
			foreach($this->ColumnArray  as $c)
			{
				//Calc max
				$maxheightblocktext=max($maxheightblocktext,(imagefontwidth(3)*strlen($c)));
				//Check if max is larger then the given pading
				if($this->cfg['ImgPaddingBottom'] < $maxheightblocktext)
				{
					$this->cfg['ImgPaddingBottom']=$maxheightblocktext;
				} //End if($this->cfg['ImgPaddingBottom'] < $maxheightblocktext)

			} //End foreach($this->ColumnArray  as $c)

		} //End if($this->cfg['BlockTextVert']==1)

		/* BoxDimensions Calculations */
		$this->BoxDimensions['bsx'] = $this->cfg['ImgPaddingLeft'];
		$this->BoxDimensions['bsy'] = $this->cfg['ImgPaddingTop'];
		$this->BoxDimensions['bex'] = $this->cfg['ImgWidth']-$this->cfg['ImgPaddingRight'];
		$this->BoxDimensions['bey'] = $this->cfg['ImgHeight']-$this->cfg['ImgPaddingBottom'];
		$this->BoxDimensions['bwidth'] = ($this->cfg['ImgWidth']-$this->cfg['ImgPaddingLeft'])-$this->cfg['ImgPaddingRight'];
		$this->BoxDimensions['bheight'] = ($this->cfg['ImgHeight']-$this->cfg['ImgPaddingTop'])-$this->cfg['ImgPaddingBottom'];

		/* Check For Compare */
		if(!empty($this->IncommingCompareArray))
		{
			$CompareCheck = true;

			//Extrac Compare array
			if(!$this->ExtractCompareArray($this->IncommingCompareArray)) { $Check = false; $this->LogArray[] = "Posted Compare Array is not a valid DataArray!"; }

			//Extra Compare Checks
			if(count($this->DataArray)!=count($this->CompareDataArray)) { $Check = false; $this->LogArray[] = "Data and Compare array's are not equal!"; }

			/* Check CompareDataArray array */
			foreach($this->CompareDataArray as $val)
			{
				if(!is_numeric($val)) { $Check = false; $this->LogArray[] = "Not all posted CompareDataArray values are numeric!"; }
			} //End foreach($this->CompareDataArray as $val)

		} //End if(!empty($this->IncommingCompareArray))

		/* if Check is ok calculate graph */
		if($Check)
		{

			/* Calculate Highest value & Avarage value */
			$tmpAr = $this->DataArray;

			//Calc Avarage
			$this->Avarage = array_sum($tmpAr)/count($tmpAr);

			//Calc Highest Value
			rsort($tmpAr);
			$this->High = $tmpAr[0]+($tmpAr[0]/20);

			/* Compare Calculations */
			if(!empty($this->IncommingCompareArray))
			{
				/* Calculate Compare */
				$tmpCompareAr = $this->CompareDataArray;

				//Calc Avarage
				$this->CompareAvarage = array_sum($tmpCompareAr)/count($tmpCompareAr);

				//Calc Highest Value
				rsort($tmpCompareAr);
				$this->CompareHigh = $tmpCompareAr[0]+($tmpCompareAr[0]/20);
				if($this->High < $this->CompareHigh) { $this->High = $this->CompareHigh; }

					/* Grid Calculations */
					$compare_ColumnCount = count($this->ColumnArray);
					$compare_ColumnWidth = ($this->BoxDimensions['bwidth']/$compare_ColumnCount);
					$compare_GridStartX = $this->BoxDimensions['bsx'];
					$compare_ColStartX = $this->BoxDimensions['bsx'];
					$compare_TotalWidth = (($this->cfg['ImgWidth']-$this->cfg['ImgPaddingLeft'])-$this->cfg['ImgPaddingRight']);
					$compare_TotalHeight = (($this->cfg['ImgHeight']-$this->cfg['ImgPaddingTop'])-$this->cfg['ImgPaddingBottom']);
					$compare_OnePercent = ($compare_TotalHeight/100);
					$compare_PrevY = $this->BoxDimensions['bey'];

					for($i=0; $i<$compare_ColumnCount; $i++)
					{
						$Value = $this->CompareDataArray[$i];
						if($Value!=0) {	$compare_Percentage = round((($Value/$this->High)*100)); } else { $compare_Percentage = 0; }
						$compare_csy = $compare_Percentage*$compare_OnePercent;
						$compare_csy = ($this->cfg['ImgHeight']-$this->cfg['ImgPaddingBottom'])-$compare_csy;
						$compare_Column = array();
						$compare_ColumnCt = array();
						$compare_Column['value'] = $this->CompareDataArray[$i].$this->cfg['UnitOfMeasure'];
						$compare_Column['prevy'] = $compare_PrevY;
						$compare_Column['csx'] = $compare_ColStartX;
						$compare_Column['csy'] = $compare_csy;
						$compare_Column['cex'] = $compare_ColStartX+$compare_ColumnWidth;
						$compare_Column['cey'] = $this->BoxDimensions['bey'];
						$compare_PrevY = $compare_csy;
						$compare_ColStartX = $compare_ColStartX+$compare_ColumnWidth;
						$this->CompareBlockDimensions[] = $compare_Column;

					} //End for($i=0; $i<$ColumnCount; $i++)

					/* Compare Avarage */
					$Value = $this->CompareAvarage;
					if($Value!=0) {	$compare_Percentage = round((($Value/$this->High)*100)); } else { $compare_Percentage = 0; }
					$compare_csy = $compare_Percentage*$compare_OnePercent;
					$compare_csy = ($this->cfg['ImgHeight']-$this->cfg['ImgPaddingBottom'])-$compare_csy;
					$this->CompareAvarageDimensions['value'] = round($this->CompareAvarage).$this->cfg['UnitOfMeasure'];
					$this->CompareAvarageDimensions['asx'] = $this->BoxDimensions['bsx'];
					$this->CompareAvarageDimensions['asy'] = $compare_csy;
					$this->CompareAvarageDimensions['aex'] = $this->BoxDimensions['bex'];
					$this->CompareAvarageDimensions['aey'] = $compare_csy;

			} //End if(!empty($this->IncommingCompareArray))

			/* Grid Calculations */
			$ColumnCount = count($this->ColumnArray);
			$ColumnWidth = ($this->BoxDimensions['bwidth']/$ColumnCount);
			$GridStartX = $this->BoxDimensions['bsx'];
			$ColStartX = $this->BoxDimensions['bsx'];
			$TotalWidth = (($this->cfg['ImgWidth']-$this->cfg['ImgPaddingLeft'])-$this->cfg['ImgPaddingRight']);
			$TotalHeight = (($this->cfg['ImgHeight']-$this->cfg['ImgPaddingTop'])-$this->cfg['ImgPaddingBottom']);
			$OnePercent = ($TotalHeight/100);
			$PrevY = $this->BoxDimensions['bey'];

			for($i=0; $i<$ColumnCount; $i++)
			{
				$GridLine = array();
				$GridLine['gsx'] = $GridStartX;
				$GridLine['gsy'] = $this->BoxDimensions['bsy'];
				$GridLine['gex'] = $GridStartX+$ColumnWidth;
				$GridLine['gey'] = $this->BoxDimensions['bey'];
				$GridStartX = $GridStartX+$ColumnWidth;
				$this->GridDimensions[] = $GridLine;

				$Value = $this->DataArray[$i];
				if($Value!=0) {	$Percentage = round((($Value/$this->High)*100)); } else { $Percentage = 0; }
				$csy = $Percentage*$OnePercent;
				$csy = ($this->cfg['ImgHeight']-$this->cfg['ImgPaddingBottom'])-$csy;
				$Column = array();
				$ColumnCt = array();
				$Column['value'] = $this->DataArray[$i].$this->cfg['UnitOfMeasure'];
				$Column['prevy'] = $PrevY;
				$Column['csx'] = $ColStartX;
				$Column['csy'] = $csy;
				$Column['cex'] = $ColStartX+$ColumnWidth;
				$Column['cey'] = $this->BoxDimensions['bey'];
				$PrevY = $csy;
				$ColumnCt['text'] = $this->ColumnArray[$i];
				$ColumnCt['ctsx'] = $ColStartX;
				$ColumnCt['ctey'] = $this->BoxDimensions['bey']+2;

				$ColStartX = $ColStartX+$ColumnWidth;
				$this->BlockDimensions[] = $Column;
				$this->BlockTextDimensions[] = $ColumnCt;

			} //End for($i=0; $i<$ColumnCount; $i++)

			/* AvarageDimensions */
			$Value = $this->Avarage;
			if($Value!=0) {	$Percentage = round((($Value/$this->High)*100)); } else { $Percentage = 0; }
			$csy = $Percentage*$OnePercent;
			$csy = ($this->cfg['ImgHeight']-$this->cfg['ImgPaddingBottom'])-$csy;
			$this->AvarageDimensions['value'] = round($this->Avarage).$this->cfg['UnitOfMeasure'];
			$this->AvarageDimensions['asx'] = $this->BoxDimensions['bsx'];
			$this->AvarageDimensions['asy'] = $csy;
			$this->AvarageDimensions['aex'] = $this->BoxDimensions['bex'];
			$this->AvarageDimensions['aey'] = $csy;

			/* HorizontalLines grid */
			$ColHeight = ($this->BoxDimensions['bheight']/4);
			$GridStartY = $this->BoxDimensions['bsy'];
			$LegendaValue = $this->High/4;
			$j = 4;
			for($i=0; $i<4; $i++)
			{
				$GridLine = array();
				$GridLine['gsx'] = $this->BoxDimensions['bsx'];
				$GridLine['gsy'] = $GridStartY;
				$GridLine['gex'] = $this->BoxDimensions['bex'];
				$GridLine['gey'] = $GridStartY+$ColHeight;

				$this->GridDimensions[] = $GridLine;

				$Legenda = array();
				$Legenda['value'] = round($LegendaValue*$j).$this->cfg['UnitOfMeasure'];
				$Legenda['lsx'] = $this->BoxDimensions['bsx']-$this->cfg['LegendaPadding'];
				$Legenda['ley'] = $GridStartY;
				$this->LegendaDimensions[] = $Legenda;
				$GridStartY = $GridStartY+$ColHeight;
				$j--;
			} //End for($i=0; $i<4; $i++)

			/* LegendaDimensions */

		} //End if($Check)

		/* Return Check */
		return $Check;

	} //End function CheckConfiguration()

//-------------------------------------------------------------------------------

	/* CreateImg */
	function CreateImg()
	{

		/* Create Img Pointer */
		$this->ip = imagecreate($this->cfg['ImgWidth'],$this->cfg['ImgHeight']);

		/* Create ImgBackgroundColor Colors */
		$Color = $this->ConvertHex($this->cfg['ImgBackgroundColor']);
		$this->ColorArray['ImgBackgroundColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create ImgTextColor Colors */
		$Color = $this->ConvertHex($this->cfg['ImgTextColor']);
		$this->ColorArray['ImgTextColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create BoxBackgroundColor Colors */
		$Color = $this->ConvertHex($this->cfg['BoxBackgroundColor']);
		$this->ColorArray['BoxBackgroundColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create BoxBorderColor Colors */
		$Color = $this->ConvertHex($this->cfg['BoxBorderColor']);
		$this->ColorArray['BoxBorderColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create BoxGridColor Colors */
		$Color = $this->ConvertHex($this->cfg['BoxGridColor']);
		$this->ColorArray['BoxGridColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create BoxTextColor Colors */
		$Color = $this->ConvertHex($this->cfg['BoxTextColor']);
		$this->ColorArray['BoxTextColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create ColumnColor Colors */
		$Color = $this->ConvertHex($this->cfg['ColumnColor']);
		$this->ColorArray['ColumnColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create AvarageColor Colors */
		$Color = $this->ConvertHex($this->cfg['AvarageColor']);
		$this->ColorArray['AvarageColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create ImgTitleColor Colors */
		$Color = $this->ConvertHex($this->cfg['ImgTitleColor']);
		$this->ColorArray['ImgTitleColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

		/* Create CompareColumnColor Colors */
		$Color = $this->ConvertHex($this->cfg['CompareColumnColor']);
		$this->ColorArray['CompareColumnColor'] = imagecolorallocate($this->ip,$Color['r'],$Color['g'],$Color['b']);

	} //End function CreateImg()

//-------------------------------------------------------------------------------

	/* PrintImg */
	function PrintImg()
	{

		/* Becnhmark */
		if($this->cfg['BenchMark']==1)
		{
			$BenchStop = $this->getmicrotime();
			$BenchTime = round($BenchStop-$this->BenchStart,4);
			$BenchWidth = (imagefontwidth(2)*strlen($BenchTime));
			$BenchX = $this->cfg['ImgWidth']-$BenchWidth;
			imagestring($this->ip,1,$BenchX ,0,$BenchTime,$this->ColorArray['ImgTitleColor']);
		} //End if($this->cfg{'']))

		/* Create Pic */
		$name = md5(microtime()).".png";
		$PicFileName = $this->cfg['tmpDir'].$name;
		$PicUrl = $this->cfg['wwwDir'].$name;
		imageinterlace($this->ip,1);

		/* Parse Type */
		switch($this->cfg['Parse'])
		{
			//Display html img
			case "1":
				imagepng($this->ip,$PicFileName);
				$this->Buffer .= str_replace('__URL__', $PicUrl, $this->cfg['Template']);
			break;

			default:
				imagepng($this->ip);
			break;
		} //End switch($this->cfg['Parse'])

		imageDestroy($this->ip);

	} //End function PrintImg()

//-------------------------------------------------------------------------------

	/* Create LegendaBlock */
	function CreateLegendaBlock()
	{
		if($this->cfg['LegendaBlockTrigger']==1)
		{
			$StrLng = strlen($this->cfg['LegendaBlockText']);
			$Len = 20+($StrLng*7);

			imagerectangle($this->ip,$this->BoxDimensions['bsx'],$this->BoxDimensions['bey']+20,$this->BoxDimensions['bsx']+$Len,$this->BoxDimensions['bey']+40,$this->ColorArray['BoxBorderColor']);
			imagefilledrectangle($this->ip,$this->BoxDimensions['bsx']+5,$this->BoxDimensions['bey']+25,$this->BoxDimensions['bsx']+15,$this->BoxDimensions['bey']+35,$this->ColorArray['ColumnColor']);
			imagerectangle($this->ip,$this->BoxDimensions['bsx']+5,$this->BoxDimensions['bey']+25,$this->BoxDimensions['bsx']+15,$this->BoxDimensions['bey']+35,$this->ColorArray['BoxBorderColor']);
			imagestring($this->ip, 2,$this->BoxDimensions['bsx']+20,$this->BoxDimensions['bey']+24,$this->cfg['LegendaBlockText'],$this->ColorArray['ImgTextColor']);

			//Check Compare Text
			if(!empty($this->cfg['CompareLegendaBlockText']))
			{
				$AddLength = $Len+10;
				$StrLng = strlen($this->cfg['CompareLegendaBlockText']);
				$CompareLen = 20+($StrLng*7);
				imagerectangle($this->ip,$AddLength+$this->BoxDimensions['bsx'],$this->BoxDimensions['bey']+20,$AddLength+$this->BoxDimensions['bsx']+$CompareLen,$this->BoxDimensions['bey']+40,$this->ColorArray['BoxBorderColor']);
				imagefilledrectangle($this->ip,$AddLength+$this->BoxDimensions['bsx']+5,$this->BoxDimensions['bey']+25,$AddLength+$this->BoxDimensions['bsx']+15,$this->BoxDimensions['bey']+35,$this->ColorArray['CompareColumnColor']);
				imagerectangle($this->ip,$AddLength+$this->BoxDimensions['bsx']+5,$this->BoxDimensions['bey']+25,$AddLength+$this->BoxDimensions['bsx']+15,$this->BoxDimensions['bey']+35,$this->ColorArray['BoxBorderColor']);
				imagestring($this->ip, 2,$AddLength+$this->BoxDimensions['bsx']+20,$this->BoxDimensions['bey']+24,$this->cfg['CompareLegendaBlockText'],$this->ColorArray['ImgTextColor']);

			} //End if(!empty($this->cfg['CompareLegendaBlockText']))

		} //End if($this->cfg['LegendaBlockTrigger']==1)

		/*
		if(!isset($this->cfg['LegendaBlockPadding'])) { $this->cfg['LegendaBlockPadding'] = "20"; }
		if(!isset($this->cfg['LegendaBlockTrigger'])) { $this->cfg['LegendaBlockTrigger'] = "1"; }
		if(empty($this->cfg['LegendaBlockText'])) { $this->cfg['LegendaBlockText'] = ""; }
		if(empty($this->cfg['CompareLegendaBlockText'])) { $this->cfg['CompareLegendaBlockText'] = ""; }
		*/
	} //End function CreateLegendaBlock()

//-------------------------------------------------------------------------------

	/* CreateTitle */
	function CreateTitle()
	{
		if($this->cfg['TitleTextTrigger']==1)
		{
			imagestring($this->ip, 2,2,2,$this->cfg['ImgTitle'],$this->ColorArray['ImgTitleColor']);
		} //End if($this->cfg['TitleTextTrigger']==1)

	} //End function CreateTitle()

//-------------------------------------------------------------------------------

	/* LegendaDimensions */
	function CreateLegenda()
	{
		if($this->cfg['LegendaTextTrigger']==1)
		{
			foreach($this->LegendaDimensions as $Col)
			{
				imagestring($this->ip, 2,$Col['lsx'],$Col['ley']-4,$Col['value'],$this->ColorArray['ImgTextColor']);
			} //End foreach($this->GridDimensions as $Grid)
		} //End if($this->cfg['ColumnTextTrigger']==1)

	} //End function CreateLegenda()

//-------------------------------------------------------------------------------

	/* Create Avarage Line */
	function CreateAvarage()
	{
		if($this->cfg['AvarageTrigger']==1)
		{
			//Default color
			$SetColor = $this->ColorArray['AvarageColor'];
			//If compare then set avaragecolor as columncolor
			if(!empty($this->IncommingCompareArray))
			{
				$SetColor = $this->ColorArray['ColumnColor'];
			} //End if(!empty($this->IncommingCompareArray))

			imageline($this->ip,$this->AvarageDimensions['asx'],$this->AvarageDimensions['asy'],$this->AvarageDimensions['aex'],$this->AvarageDimensions['aey'],$SetColor);
			$String = $this->AvarageDimensions['value'];
			imagestring($this->ip, 2,$this->AvarageDimensions['aex']+3,$this->AvarageDimensions['asy']-6, $String,$this->ColorArray['ColumnColor']);
		} //End if($this->cfg['AvarageTrigger']==1)

	} //End function CreateAvarage()

//-------------------------------------------------------------------------------

	/* Create Compare Avarage Line */
	function CreateCompareAvarage()
	{
		if( ($this->cfg['AvarageTrigger']==1) && (!empty($this->CompareAvarageDimensions)) )
		{
			imageline($this->ip,$this->CompareAvarageDimensions['asx'],$this->CompareAvarageDimensions['asy'],$this->CompareAvarageDimensions['aex'],$this->CompareAvarageDimensions['aey'],$this->ColorArray['CompareColumnColor']);
			imagestring($this->ip, 2,$this->CompareAvarageDimensions['asx']+3,$this->CompareAvarageDimensions['asy']-16, $this->CompareAvarageDimensions['value'],$this->ColorArray['CompareColumnColor']);
		} //End if($this->cfg['AvarageTrigger']==1)

	} //End function CompareAvarageDimensions()

//-------------------------------------------------------------------------------

	/* Create CreateBlockText */
	function CreateBlockText()
	{
		if($this->cfg['ColumnTextTrigger']==1)
		{
			foreach($this->BlockTextDimensions as $ColText)
			{
				//Vertical blocktext
				if($this->cfg['BlockTextVert']==1)
				{
					imagestringup($this->ip, 2,$ColText['ctsx'],($ColText['ctey']+(imagefontwidth(2)*strlen($ColText['text']))),$ColText['text'],$this->ColorArray['ImgTextColor']);
				} //End if($this->cfg['BlockTextVert']==1)
				else
				{
					imagestring($this->ip,2,$ColText['ctsx'],$ColText['ctey'],$ColText['text'],$this->ColorArray['ImgTextColor']);
				} //End else if ($this->cfg['BlockTextVert'])

			} //End foreach($this->GridDimensions as $Grid)
		} //End if($this->cfg['BlockTextTrigger']==1)

	} //End function CreateColumnText()

//-------------------------------------------------------------------------------

	/* CreateLine */
	function CreateLine()
	{
		foreach($this->BlockDimensions as $Col)
		{
			imageline($this->ip,$Col['csx'],$Col['prevy'],$Col['cex'],$Col['csy'],$this->ColorArray['ColumnColor']);

		} //End foreach($this->BlockDimensions as $Col)

	} //End function CreateLine()

//-------------------------------------------------------------------------------

	/* CreateCompareLine */
	function CreateCompareLine()
	{
		foreach($this->CompareBlockDimensions as $Col)
		{
			imageline($this->ip,$Col['csx'],$Col['prevy'],$Col['cex'],$Col['csy'],$this->ColorArray['CompareColumnColor']);

		} //End foreach($this->CompareBlockDimensions as $Col)

	} //End function CreateCompareLine()

//-------------------------------------------------------------------------------

	/* CreatePoly */
	function CreatePoly()
	{
		foreach($this->BlockDimensions as $Col)
		{
			$values = array(
			  0  => $Col['csx'],    // x1
			  1  => $Col['prevy'],  // y1
			  2  => $Col['cex'],    // x2
			  3  => $Col['csy'],    // y2
			  4  => $Col['cex'],    // x3
			  5  => $Col['cey'],    // y3
			  6  => $Col['csx'],    // x4
			  7  => $Col['cey'],    // y4
			  8  => $Col['csx'],    // x5
			  9  => $Col['prevy']   // y5
		);
			imagefilledpolygon($this->ip, $values, 5,$this->ColorArray['ColumnColor']);

			//imageline($this->ip,$Col['csx'],$Col['prevy'],$Col['cex'],$Col['csy'],$this->ColorArray['ColumnColor']);

		} //End foreach($this->BlockDimensions as $Col)

	} //End function CreatePoly()

//-------------------------------------------------------------------------------

	/* Create Block */
	function CreateBlock()
	{
		foreach($this->BlockDimensions as $Col)
		{
			imagefilledrectangle($this->ip,$Col['csx'],$Col['csy'],$Col['cex'],$Col['cey'],$this->ColorArray['ColumnColor']);

		} //End foreach($this->BlockDimensions as $Col)

	} //End function CreateBlock()

//-------------------------------------------------------------------------------

	/* Create Column Text */
	function CreateColumnText()
	{
		if($this->cfg['BlockTextTrigger']==1)
		{
			foreach($this->BlockDimensions as $Col)
			{
				imagestringup($this->ip, 2,$Col['csx'],$Col['cey']-4,$Col['value'],$this->ColorArray['BoxTextColor']);
			} //End foreach($this->BlockDimensions as $Col)

		} //End if($this->cfg['ColumnTextTrigger']==1)

	} //End function CreateRectangles()

//-------------------------------------------------------------------------------

	/* CreateGrid */
	function CreateGrid()
	{

		if($this->cfg['GridTrigger']==1)
		{
			/* Vertical Lines */
			foreach($this->GridDimensions as $Grid)
			{
				//$style = array ($this->ColorArray['BoxGridColor'],$this->ColorArray['BoxGridColor'],$this->ColorArray['BoxGridColor'],$this->ColorArray['BoxBackgroundColor'],$this->ColorArray['BoxGridColor'],$this->ColorArray['BoxGridColor'],$this->ColorArray['BoxGridColor']);
				//imagesetstyle ($this->ip, $style);
				//imagerectangle($this->ip,$Grid['gsx'],$Grid['gsy'],$Grid['gex'],$Grid['gey'],IMG_COLOR_STYLED);
				imagerectangle($this->ip,$Grid['gsx'],$Grid['gsy'],$Grid['gex'],$Grid['gey'],$this->ColorArray['BoxGridColor']);

			} //End foreach($this->GridDimensions as $Grid)

		} //End if($this->cfg['GridTrigger']==1)

	} //End function CreateGrid()

//-------------------------------------------------------------------------------

	/* Create Box Background */
	function CreateBoxBackground()
	{
		/* Build Box Rectangle Background */
		imagefilledrectangle($this->ip,$this->BoxDimensions['bsx'],$this->BoxDimensions['bsy'],$this->BoxDimensions['bex'],$this->BoxDimensions['bey'],$this->ColorArray['BoxBackgroundColor']);
	} //End function CreateBox()

//-------------------------------------------------------------------------------

	/* Create Box Border */
	function CreateBoxBorder()
	{
		/* Create Box Border */
		imagerectangle($this->ip,$this->BoxDimensions['bsx'],$this->BoxDimensions['bsy'],$this->BoxDimensions['bex'],$this->BoxDimensions['bey'],$this->ColorArray['BoxBorderColor']);

	} //End function CreateBox()

//-------------------------------------------------------------------------------

	/*Convert hex color code to rgb array*/
	function ConvertHex($hex)
	{

  		//Replace #
		$color = str_replace('#','',$hex);

		//Define rgb
 	 	$rgb = array (
				'r' => hexdec(substr($color,0,2)),
				'g' => hexdec(substr($color,2,2)),
				'b' => hexdec(substr($color,4,2))
				);
		//return
		return $rgb;

	} //End function hex2rgb($hex)

//-------------------------------------------------------------------------------

	/* BlockGraph */
	function BlockGraph($DataArray,$cfg=array())
	{
		/* Set Data & Columns */
		$this->cfg = $cfg;
		$this->IncommingArray = $DataArray;

		/* Check Configuration */
		if($this->CheckConfiguration())
		{
			/* CreateImg */
			$this->CreateImg();

			/* Create Box Background*/
			$this->CreateBoxBackground();

			/* Legenda */
			$this->CreateLegenda();

			/* Create Line Columns */
			$this->CreateBlock();

			/* Create Grid */
			$this->CreateGrid();

			/* Create CreateAvarage */
			$this->CreateAvarage();

			/* Create Block Text */
			$this->CreateBlockText();

			/* Create Column Text */
			$this->CreateColumnText();

			/* Create Box Border*/
			$this->CreateBoxBorder();

			/* Title */
			$this->CreateTitle();

			/* Print Img */
			$this->PrintImg();

		} //End if($this->CheckConfiguration())
		else
		{
			$this->ErrorImg();
		}

	} //End function BlockGraph($ColumnArray,$DataArray,$cfg=array())

//-------------------------------------------------------------------------------

	/* LineGraph */
	function LineGraph($DataArray,$cfg=array())
	{
		/* Set Data & Columns */
		$this->cfg = $cfg;
		$this->IncommingArray = $DataArray;

		/* Check Configuration */
		if($this->CheckConfiguration())
		{
			/* CreateImg */
			$this->CreateImg();

			/* Create Box Background*/
			$this->CreateBoxBackground();

			/* Legenda */
			$this->CreateLegenda();

			/* Create Line Columns */
			$this->CreateLine();

			/* Create Grid */
			$this->CreateGrid();

			/* Create CreateAvarage */
			$this->CreateAvarage();

			/* Create Block Text */
			$this->CreateBlockText();

			/* Create Column Text */
			$this->CreateColumnText();

			/* Create Box Border*/
			$this->CreateBoxBorder();

			/* Title */
			$this->CreateTitle();

			/* Print Img */
			$this->PrintImg();

		} //End if($this->CheckConfiguration())
		else
		{
			$this->ErrorImg();
		}

	} //End function LineGraph($ColumnArray,$DataArray)

//-------------------------------------------------------------------------------

	/* PolyGraph */
	function PolyGraph($DataArray,$cfg=array())
	{
		/* Set Data & Columns */
		$this->cfg = $cfg;
		$this->IncommingArray = $DataArray;

		/* Check Configuration */
		if($this->CheckConfiguration())
		{
			/* CreateImg */
			$this->CreateImg();

			/* Create Box Background*/
			$this->CreateBoxBackground();

			/* Legenda */
			$this->CreateLegenda();

			/* Create Line Columns */
			$this->CreatePoly();

			/* Create Grid */
			$this->CreateGrid();

			/* Create CreateAvarage */
			$this->CreateAvarage();

			/* Create Block Text */
			$this->CreateBlockText();

			/* Create Column Text */
			$this->CreateColumnText();

			/* Create Box Border*/
			$this->CreateBoxBorder();

			/* Title */
			$this->CreateTitle();

			/* Print Img */
			$this->PrintImg();

		} //End if($this->CheckConfiguration())
		else
		{
			$this->ErrorImg();
		}

	} //End function PolyGraph($ColumnArray,$DataArray)

//-------------------------------------------------------------------------------

	/* CompareGraph */
	function CompareGraph($DataArray,$DataArrayCompare,$cfg=array())
	{
		/* Set Data & Columns */
		$this->cfg = $cfg;
		$this->IncommingArray = $DataArray;
		$this->IncommingCompareArray = $DataArrayCompare;

		/* Check Configuration */
		if($this->CheckConfiguration())
		{
			/* CreateImg */
			$this->CreateImg();

			/* Create Box Background*/
			$this->CreateBoxBackground();

			/* Legenda */
			$this->CreateLegenda();

			/* Create Line Columns */
			$this->CreateLine();
			$this->CreateCompareLine();

			/* Create Grid */
			$this->CreateGrid();

			/* Create CreateAvarage */
			$this->CreateAvarage();
			$this->CreateCompareAvarage();

			/* Create Block Text */
			$this->CreateBlockText();

			/* Create Column Text */
			$this->CreateColumnText();

			/* Create Box Border*/
			$this->CreateBoxBorder();

			/* Title */
			$this->CreateTitle();

			$this->CreateLegendaBlock();

			/* Print Img */
			$this->PrintImg();

		} //End if($this->CheckConfiguration())
		else
		{
			$this->ErrorImg();
		}

	} //End function CompareGraph($DataArray,$DataArrayCompare,$cfg=array())

//-------------------------------------------------------------------------------

	function ErrorImg()
	{
		$NrOfLog = count($this->LogArray);
		$Height = $NrOfLog*20;
		$this->cfg['ImgWidth'] = "600";
		$this->cfg['ImgHeight'] = $Height;

		/* CreateImg */
		$this->CreateImg();
		$Xstart = 5;
		$Ystart = 5;
		$Yoffset = 10;
		foreach($this->LogArray as $Log)
		{
			imagestring($this->ip,2,$Xstart,$Ystart,$Log,$this->ColorArray['AvarageColor']);
			$Ystart = $Ystart+$Yoffset;
		}

		/* Print Img */
		$this->PrintImg();

	} //End function ErrorImg()

//-------------------------------------------------------------------------------

	/*Exract array*/
	function ExtractArray($Array)
	{

		//Check
		if(is_array($Array))
		{

			//Dims
			$NameArray = array();
			$ValueArray = array();

			//Loop array
			while(list($key,$value)=each($Array))
			{
				//Add values to seperate arrays
				$NameArray[] = $key;
				$ValueArray[] = $value;
			} //End while(list($key,$value)=each($Array)

			$this->ColumnArray = $NameArray;
			$this->DataArray = $ValueArray;

			return true;
		} //End if(is_array($Array))
		else
		{
			return false;
		} //End if(is_array($Array))

	} //End function ExtractArray($Array)

//-------------------------------------------------------------------------------

	/*Exract array*/
	function ExtractCompareArray($Array)
	{

		//Check
		if(is_array($Array))
		{

			//Dims
			$NameArray = array();
			$ValueArray = array();

			//Loop array
			while(list($key,$value)=each($Array))
			{
				//Add values to seperate arrays
				$NameArray[] = $key;
				$ValueArray[] = $value;
			} //End while(list($key,$value)=each($Array)

			$this->CompareDataArray = $ValueArray;

			return true;
		} //End if(is_array($Array))
		else
		{
			return false;
		} //End if(is_array($Array))

	} //End function ExtractArray($Array)

//-------------------------------------------------------------------------------

	//Microtime
	function getmicrotime()
	{
	   	list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	} //End function getmicrotime()

//-------------------------------------------------------------------------------

	function getBuffer() {
		return $this->Buffer;
	}

	function clearBuffer() {
		$this->Buffer = '';
	}

//-------------------------------------------------------------------------------

} //End class WikiGraph

?>
