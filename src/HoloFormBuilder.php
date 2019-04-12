<?php

/**
 * @author William Raendchen
 * @date 2019-04-11
 *
 * This class can take an Eloquent model and check the following information:
 * - column names
 * - column types
 * - columns that have a foreign key
 * - the available entries in the referenced foreign key column
 *   (to provide options for HTML select elements)
 *
 * Requirements:
 * - Laravel ^5.7
 * - Model connection must use MySQL driver
 * - the user specified in your model connection needs read/write access to the model's table (obviously)
 * - the same user also needs read access to the ***information_schema*** database on the same server
 *   (the information about referenced columns is fetched from information_schema.KEY_COLUMN_USAGE)
 *
 * The information_schema connection is created dynamically and directly derived from your model connection.
 */

namespace Holonaut/HoloForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\CustomClasses\HoloHelper;
use App\CustomClasses\HoloForm;

class HoloFormBuilder
{
	const SUPPORTED_DRIVERS = ['mysql'];

	/**
	 * @param \App\CustomClasses\HoloForm $form
	 * @param Model $model
	 * @return \App\CustomClasses\HoloForm
	 */
	public static function fieldsFromModel(HoloForm $form, Model $model)
	{
		// get the model's configured connection
		$modelConfig = $model->getConnection()->getConfig();

		// throw exception if the model's driver is not supported
		if(!in_array($modelConfig['driver'], static::SUPPORTED_DRIVERS, true)){
			throw new \RuntimeException(
				'Driver \'' . $modelConfig['driver'] . '\'' .
				'(used by Model ' . $model->getMorphClass() . ') not supported in ' . __CLASS__ . '.
				Supported drivers: ' . implode(', ', static::SUPPORTED_DRIVERS)
			);
		}

		// adjust the connection name and config index
		$informationSchemaName = $modelConfig['name'] . 'informationSchema';
		$informationSchemaConfigIndex = 'database.connections.' . $informationSchemaName;

		// create a new connection with the same config, adjust name and database
		Config::set("$informationSchemaConfigIndex", $modelConfig); // copy the config
		Config::set("$informationSchemaConfigIndex.name", $informationSchemaName); // adjust the name
		Config::set("$informationSchemaConfigIndex.database", 'information_schema'); // adjust the database

		$columns = DB::select('describe ' . $model->getTable());

		foreach ($columns as $column){
			$fkExistingEntries = [];

			// find this tablecolumn in information schema and get the referenced table and column
			$fkUsage = DB::connection($informationSchemaName)
				->table('KEY_COLUMN_USAGE')
				->where('TABLE_NAME', '=', $model->getTable())
				->where('COLUMN_NAME', '=', $column->Field)
				->get(['REFERENCED_TABLE_NAME as table', 'REFERENCED_COLUMN_NAME as column'])
				->first()
			;
			$usesForeignKey = (bool) $fkUsage && $fkUsage->table && $fkUsage->column;

			// if a foreign key was found, get all entries in the foreign column
			// these will be handed to a select-element
			if($usesForeignKey){
				$fkExistingEntries = DB::connection('mysql')
					->table($fkUsage->table)
					->get([$fkUsage->column])
					->pluck($fkUsage->column)
					->toArray()
				;
			}

			$form->addInput(
				$column->Field,
				$usesForeignKey ? 'select' : static::htmlType($column->Type, $column->Field),
				$column->Default,
				($column->Null == 'NO') ? 'required' : '',
				($column->Null == 'NO') ? 'required' : '',
				$fkExistingEntries ?? null
			);
		}

		return $form;
	}

	public static function htmlType($sqlType, $fieldName = '')
	{
		if (HoloHelper::contains($fieldName, 'password') ||
			HoloHelper::contains($fieldName, 'token'))
		{
			return 'password';
		}

    	switch ($sqlType) {
			case HoloHelper::contains($sqlType, 'varchar'):
				return 'text';
				break;
			case HoloHelper::contains($sqlType, 'int'):
				return 'number';
				break;
			default:
				throw new \RuntimeException('htmlType not defined for sqlType' . $sqlType);
		}
	}
}