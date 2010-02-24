<?php
/**
 * @package Base
 * @subpackage Member
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 * Include Report.php so that ReportControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");
require_once("Atrox/Core/Data/Report.php");


/**
 *
 * @author Robert Arnold (Clock Ltd) {@link mailto:robert.arnold@clock.co.uk robert.arnold@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Member
 */
class MemberStatisticsControl extends ReportControl {
	
	function retrieveMemberAgeGroups($groupByGender = false) {
		
		$memberControl = BaseFactory::getMemberControl();
		$filter = CoreFactory::getFilter();
		$filter->addConditional($memberControl->table, "Blocked", "f");				
		$filter->addOrder("DateOfBirth");		
		$memberControl->setFilter($filter);
		$memberControl->retrieveAll();
		$ages = array();
		
		$ageGroup[] = array("Start" => 0, "End" => 10, "Count" => 0, "Description" => "0 - 10", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);			
		$ageGroup[] = array("Start" => 11, "End" => 20, "Count" => 0, "Description" => "12 - 20", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		$ageGroup[] = array("Start" => 21, "End" => 30, "Count" => 0, "Description" => "21 - 30", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		$ageGroup[] = array("Start" => 31, "End" => 40, "Count" => 0, "Description" => "31 - 40", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		$ageGroup[] = array("Start" => 41, "End" => 50, "Count" => 0, "Description" => "41 - 50", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		$ageGroup[] = array("Start" => 50, "End" => 1000, "Count" => 0, "Description" => "51+", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		$ageGroup[] = array("Start" => -1, "End" => -1, "Count" => 0, "Description" => "Unknown", "MaleCount" => 0, "FemaleCount" => 0, "UnknownGenderCount" => 0);
		
		while ($member = $memberControl->getNext()) {				
			
			if ($member->get("DateOfBirth") == "") {
				$age = -1;									
			} else {
				$mathControl = CoreFactory::getMathControl();
				$age = $mathControl->getAge($member->get("DateOfBirth"));					
			}				
			$gender = $member->get("Gender");
			
			for ($i = 0; $i < sizeof($ageGroup); $i++) {
				if (($age >= $ageGroup[$i]["Start"]) 
						&& ($age <= $ageGroup[$i]["End"])) {
					$ageGroup[$i]["Count"]++;
					switch ($gender) {
						case MEMBER_GENDER_MALE:
							$ageGroup[$i]["MaleCount"]++;
							break;
						case MEMBER_GENDER_FEMALE:
							$ageGroup[$i]["FemaleCount"]++;
							break;								
						default:
							$ageGroup[$i]["UnknownGenderCount"]++;
							break;
					}						
				} 
			} continue 1;				
		}			
		return $ageGroup;
	}	
	
	function retrieveGenderGroups() {
		$memberControl = BaseFactory::getMemberControl();
		$memberControl->retrieveAll();
		$genders = array();			
		$genderGroup[] = array("Count" => 0, "Description" => "Male");
		$genderGroup[] = array("Count" => 0, "Description" => "Female");
		$genderGroup[] = array("Count" => 0, "Description" => "Unknown");
		while ($member = $memberControl->getNext()) {
			if ($member->get("Gender") == MEMBER_GENDER_MALE) {
				$genderGroup[0]["Count"]++;
			} else if ($member->get("Gender") == MEMBER_GENDER_FEMALE) {
				$genderGroup[1]["Count"]++;
			} else {
				$genderGroup[2]["Count"]++;
			}
		}	
		return $genderGroup;
	}
	
	function createAgeGroupPieChart(){
		require_once "Image/Graph.php";

		$ages = $this->retrieveMemberAgeGroups();
		
		
		// create the graph
		$graph =& Image_Graph::factory("graph", array(250, 350));
		// add a TrueType font
		$font =& $graph->addNew("font", "Verdana");
		// set the font size to 11 pixels
		$font->setSize(8);
		
		// setup the plotarea, legend and their layout
		$graph->add(
			Image_Graph::vertical(
				 $plotarea = Image_Graph::factory("plotarea"),
				 $legend = Image_Graph::factory("legend"),
				 70
			)
		);
		$legend->setPlotArea($plotarea);
		$plotarea->hideAxis();
		
		// create the dataset
		$dataset =& Image_Graph::factory("dataset");  			
		for ($i = 0; $i < sizeof($ages); $i++) {
			if ($ages[$i]["Count"] > 0) {
				$dataset->addPoint($ages[$i]["Description"],  $ages[$i]["Count"]);
			}									
		}
		
		// create the 1st plot as smoothed area chart using the 1st dataset
		$plot =& $plotarea->addNew("Image_Graph_Plot_Pie", $dataset);
		
		//$plot->setRestGroup(11, "Other animals");
		
		$plot->Radius = 2;
				
		// set a line color
		$plot->setLineColor("gray");
		
		// set a standard fill style
		$fillArray =& Image_Graph::factory("Image_Graph_Fill_Array");
		$plot->setFillStyle($fillArray);
		$fillArray->addColor("green@0.2");
		$fillArray->addColor("blue@0.2");
		$fillArray->addColor("yellow@0.2");
		$fillArray->addColor("red@0.2");
		$fillArray->addColor("orange@0.2");
		$fillArray->addColor("black@0.2", "rest");
		
		$plot->explode(10);
		
		
		// create a Y data value marker
		$marker =& $plot->addNew("Image_Graph_Marker_Value", IMAGE_GRAPH_PCT_Y_TOTAL);
		// fill it with white
		$marker->setFillColor("white");
		// and use black border
		$marker->setBorderColor("black");
		// and format it using a data preprocessor
		$marker->setDataPreprocessor(Image_Graph::factory("Image_Graph_DataPreprocessor_Formatted", "%0.1f%%"));
		$marker->setFontSize(7);
		
		// create a pin-point marker type
		$pointingMarker =& $plot->addNew("Image_Graph_Marker_Pointing_Angular", array(20, $marker));
		// and use the marker on the plot
		$plot->setMarker($pointingMarker);
		
		// output the Graph
		$graph->done();
	}	
	
	function createGenderGroupStackedChart(){
		require_once 'Image/Graph.php';			
		
		$ages = $this->retrieveMemberAgeGroups();

		// create the graph
		$graph =& Image_Graph::factory('graph', array(500, 550)); 
		// add a TrueType font
		$font =& $graph->addNew('font', 'Verdana');
		// set the font size to 11 pixels
		$font->setSize(8);
		
		$graph->setFont($font);
		
		$graph->add(
			Image_Graph::vertical(
				 $plotarea = Image_Graph::factory("plotarea"),
				 $legend = Image_Graph::factory("legend"),
				 50
			)
		);
		
		$legend->setPlotarea($plotarea);        
				
		// create the dataset
		$datasets[] =& Image_Graph::factory("dataset"); 		
		$datasets[] =& Image_Graph::factory("dataset"); 
		$datasets[] =& Image_Graph::factory("dataset"); 				
		$datasets[0]->setName("Male");
		$datasets[1]->setName("Female");	
		$datasets[2]->setName("Unknown");	
		for ($i = 0; $i < sizeof($ages); $i++) {
			$datasets[0]->addPoint($ages[$i]["Description"], $ages[$i]["MaleCount"]);
			$datasets[1]->addPoint($ages[$i]["Description"], $ages[$i]["FemaleCount"]);
			$datasets[2]->addPoint($ages[$i]["Description"], $ages[$i]["UnknownGenderCount"]);
		}	
		
		// create the 1st plot as smoothed area chart using the 1st dataset
		$plot =& $plotarea->addNew('bar', array($datasets, 'stacked'));
		
		// set a line color
		$plot->setLineColor('gray');
		
		// create a fill array   
		$fillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
		$fillArray->addColor('blue@0.2');
		$fillArray->addColor('yellow@0.2');
		$fillArray->addColor('green@0.2');
		
		// set a standard fill style
		$plot->setFillStyle($fillArray);
		
		// create a Y data value marker
		$marker =& $plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
		// and use the marker on the 1st plot
		$plot->setMarker($marker);    
		
		$plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));
		
		// output the Graph
		$graph->done(); 
	}	
}