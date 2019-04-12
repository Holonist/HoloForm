<?php
/**
 * Created by PhpStorm.
 * User: Raendchen
 * Date: 11.01.2019
 * Time: 14:51
 */

namespace Holonaut\HoloForm;

class HoloHelper
{
	// see HoloHelper::getFormFile to see what kind of $file is expected as input
	public static function getObjectsFromCsv(Array $file, $delimiter = ';')
	{
		// PARSE CSV FILE
		// easy but inflexible way
		//$objects = array_map('str_getcsv', $file, $delimiter);

		// does the same as above but with more control
		$objects = array_map(function($value) use ($delimiter) {
			return str_getcsv($value, $delimiter);
		}, $file);

		// parse column names
		$columns = array_shift($objects);

		// Create object from associative array
		foreach ($objects as $i => $row) {
			$objects[$i] = (object) array_combine($columns, $row);
		}

		return $objects;
	}

	public static function writeObjectsAsCsv(&$file, $objects, $delimiter = ';'){
		//add utf-8 BOM
		//fwrite($file, "\xEF\xBB\xBF");
		//fwrite($file, pack('CCC', 0xef, 0xbb, 0xbf));

		// csv header line
		fwrite($file, implode($delimiter, array_keys($objects[0])) . PHP_EOL);

		// csv body
		foreach($objects as $object) {
			fwrite($file, implode($delimiter, $object) . PHP_EOL);
		}
	}

	public static function transformObjectsIntoArrays($objects)
	{
		foreach($objects as &$object){
			$object = json_decode(json_encode($object), true);
		}

		return $objects;
	}

	public static function cleanObjects(array $objects, array $allowedProperties)
	{
		foreach ($objects as &$object) {
			foreach ($object as $propertyName => $propertyValue){
				if(!in_array($propertyName, $allowedProperties, true)){
					unset($object->$propertyName);
				}
			}
		} unset($object);

		return $objects;
	}

	public static function cleanArray(array $arrayToClean, array $allowedKeys) {
		foreach($arrayToClean as $key => $entry) {
			if(!in_array($key, $allowedKeys)) {
				unset($arrayToClean[$key]);
			}
		}

		return $arrayToClean;
	}

	public static function getFormFile($formId, $fileName = 'file')
	{
		return file($_FILES[$formId]['tmp_name'][$fileName], FILE_SKIP_EMPTY_LINES);
	}

	public static function contains($haystack, $needle) {
		if (strpos($haystack, $needle) !== false) return true;
		return false;
	}
}