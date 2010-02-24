<?php
/**
 * @package Core
 * @copyright 2005 Clock Ltd
 * @version 0.l
 */

/**
 * Creates a number of different graph types.
 *
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright 2005 Clock Ltd
 * @version 0.l
 * @package Core
 */
class Graph {

	/**
	 * Creates a new Bar graph called '$name' with a legend using the array '$data'.
	 *
	 * @param $name String Name of the Bar graph
	 * @param $data Array An array containing legend titles and figures for the graph.
	 * @param $width Int The width at which the graph will be shown.
	 */
	 //Commented out tom replaced 2.1 function with 2.2 function for intranet
//	function createBar($name, $data, $width = 200, $showlegend = true) {
//		// Order the legend
//		ksort($data);
//		$output = "<div class=\"graph_bar_panel\">\n<div class=\"graph_bar\" id=\"$name\">\n";
//		$legend = "<div class=\"graph_legend\" id=\"$name"."_legend\">\n";
//		$total = 0;
//		foreach ($data as $k => $v) {
//			$total += $v;
//		}
//		if ($total == 0) {
//			$total = 1;
//		}
//		$factor = $width / $total;
//		$count = 0;
//		foreach ($data as $k => $v) {
//			$count++;
//			$size = round($factor * $v);
//			$percent = round(($v / $total) * 100);
//			$output .= "<div class=\"graph_bar$count\" style=\"width: " . $size . "px;\" title=\"$k - $v ($percent%)\">&nbsp;</div>\n";
//			$legend .= "<p class=\"graph_legend$count\"><span>&nbsp</span>$k - $v ($percent%)</p>\n";
//		}
//		$output .= "</div>\n";
//		if ($showlegend) {
//			$output .= "$legend</div>\n";
//		}
//		$output .= "</div>\n";
//		return $output;
//	}
		function createBar($name, $data, $width = 200, $showlegend = true, $div = "graph_bar_panel", $colour = "graph_bar", $currency = "", $linkConstruct = "", $linkId = "", $legendDiv = "graph_legend", $sort = false) {
			
			// Order the legend
			if ($sort) {
				ksort($data);
			}
			$output = "<div class=\"$div\">\n<div class=\"graph_bar\" id=\"$name\">\n";
			$legend = "<div class=\"$legendDiv\" id=\"$name"."_legend\">\n";
			$total = 0;
			foreach ($data as $k => $v) {
				$total += $v;
			}
			if ($total == 0) {
				$total = 1;
			}
			$factor = $width / $total;
			$count = 0;
			foreach ($data as $k => $v) {
				$count++;
				$size = round($factor * $v);
				$percent = round(($v / $total) * 100);
				if ($linkConstruct != "") {
					$output .="<a href=\"" . $linkConstruct . $linkId[$v] . "\">";
					$legend .="<a href=\"" . $linkConstruct . $linkId[$v] . "\">";
				}
				$output .= "<div class=\"$colour$count\" style=\"width: " . $size . "px;\" title=\"$currency$k - $v ($percent%)\">&nbsp;</div>\n";
				$legend .= "<p class=\"$legendDiv$count\"><span>&nbsp;</span>$k - $v ($percent%)</p>\n";
				if ($linkConstruct != "") {
					$output .="</a>";
					$legend .="</a>";
				}
			}
			$output .= "</div>\n";
			if ($showlegend) {
				$output .= "$legend</div>\n";
			}
			$output .= "</div>\n";
			return $output;
		}
	/**
	 * Creates a new Bar graph called '$name' with a legend using the array '$data'.
	 *
	 * @param String $name Name of the Bar graph
	 * @param Array $data An array containing legend titles and figures for the graph.
	 * @param Int $width The width at which the graph will be shown.
	 */
	function createVBar($name, $data, $height = 200, $showlegend = true) {
		// Order the legend
		ksort($data);
		$output = "<div class=\"graph_bar_panel\">\n<div class=\"graph_bar\" id=\"$name\">\n";
		$legend = "<div class=\"graph_legend\" id=\"$name"."_legend\">\n";
		$total = 0;
		foreach ($data as $k => $v) {
			$total += $v;
		}
		$factor = $height / $total;
		$count = 0;
		foreach ($data as $k => $v) {
			$count++;
			$size = round($factor * $v);
			$percent = round(($v / $total) * 100);
			$output .= "<div class=\"graph_bar$count\" style=\"height: " . $size . "px;\" title=\"$k - $v ($percent%)\">&nbsp;</div>\n";
//			$legend .= "<p class=\"graph_legend$count\"><span>&nbsp</span>$k - $v ($percent%)</p>\n";
		}
		$output .= "</div>\n";
		if ($showlegend) {
			$output .= "$legend</div>\n";
		}
		$output .= "</div>\n";
		return $output;
	}
}