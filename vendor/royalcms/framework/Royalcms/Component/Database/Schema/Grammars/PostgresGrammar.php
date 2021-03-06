<?php namespace Royalcms\Component\Database\Schema\Grammars;

use Royalcms\Component\Support\Fluent;
use Royalcms\Component\Database\Schema\Blueprint;

class PostgresGrammar extends Grammar {

	/**
	 * The possible column modifiers.
	 *
	 * @var array
	 */
	protected $modifiers = array('Increment', 'Nullable', 'Default');

	/**
	 * The columns available as serials.
	 *
	 * @var array
	 */
	protected $serials = array('bigInteger', 'integer');

	/**
	 * Compile the query to determine if a table exists. 
	 *
	 * @return string
	 */
	public function compileTableExists()
	{
		return 'select * from information_schema.tables where table_name = ?';
	}

	/**
	 * Compile the query to determine the list of columns.
	 *
	 * @param  string  $table
	 * @return string
	 */
	public function compileColumnExists($table)
	{
		return "select column_name from information_schema.columns where table_name = '$table'";
	}

	/**
	 * Compile a create table command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileCreate(Blueprint $blueprint, Fluent $command)
	{
		$columns = implode(', ', $this->getColumns($blueprint));

		return 'create table '.$this->wrapTable($blueprint)." ($columns)";
	}

	/**
	 * Compile a create table command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileAdd(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);

		$columns = $this->prefixArray('add column', $this->getColumns($blueprint));

		return 'alter table '.$table.' '.implode(', ', $columns);
	}

	/**
	 * Compile a primary key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compilePrimary(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);

		return 'alter table '.$this->wrapTable($blueprint)." add primary key ({$columns})";
	}

	/**
	 * Compile a unique key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);

		$columns = $this->columnize($command->columns);

		return "alter table $table add constraint {$command->index} unique ($columns)";
	}

	/**
	 * Compile a plain index key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileIndex(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->columnize($command->columns);

		return "create index {$command->index} on ".$this->wrapTable($blueprint)." ({$columns})";
	}

	/**
	 * Compile a drop table command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDrop(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table '.$this->wrapTable($blueprint);
	}

	/**
	 * Compile a drop table (if exists) command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
	{
		return 'drop table if exists '.$this->wrapTable($blueprint);
	}

	/**
	 * Compile a drop column command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropColumn(Blueprint $blueprint, Fluent $command)
	{
		$columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

		$table = $this->wrapTable($blueprint);

		return 'alter table '.$table.' '.implode(', ', $columns);
	}

	/**
	 * Compile a drop primary key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
	{
		$table = $blueprint->getTable();

		return 'alter table '.$this->wrapTable($blueprint)." drop constraint {$table}_pkey";
	}

	/**
	 * Compile a drop unique key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropUnique(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);

		return "alter table {$table} drop constraint {$command->index}";
	}

	/**
	 * Compile a drop index command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropIndex(Blueprint $blueprint, Fluent $command)
	{
		return "drop index {$command->index}";
	}

	/**
	 * Compile a drop foreign key command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileDropForeign(Blueprint $blueprint, Fluent $command)
	{
		$table = $this->wrapTable($blueprint);

		return "alter table {$table} drop constraint {$command->index}";
	}

	/**
	 * Compile a rename table command.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $command
	 * @return string
	 */
	public function compileRename(Blueprint $blueprint, Fluent $command)
	{
		$from = $this->wrapTable($blueprint);

		return "alter table {$from} rename to ".$this->wrapTable($command->to);
	}

	/**
	 * Create the column definition for a char type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeChar(Fluent $column)
	{
		return "char({$column->length})";
	}

	/**
	 * Create the column definition for a string type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeString(Fluent $column)
	{
		return "varchar({$column->length})";
	}

	/**
	 * Create the column definition for a text type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeText(Fluent $column)
	{
		return 'text';
	}

	/**
	 * Create the column definition for a medium text type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeMediumText(Fluent $column)
	{
		return 'text';
	}

	/**
	 * Create the column definition for a long text type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeLongText(Fluent $column)
	{
		return 'text';
	}

	/**
	 * Create the column definition for a integer type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'serial' : 'integer';
	}

	/**
	 * Create the column definition for a big integer type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeBigInteger(Fluent $column)
	{
		return $column->autoIncrement ? 'bigserial' : 'bigint';
	}

	/**
	 * Create the column definition for a medium integer type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeMediumInteger(Fluent $column)
	{
		return 'integer';
	}

	/**
	 * Create the column definition for a tiny integer type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeTinyInteger(Fluent $column)
	{
		return 'smallint';
	}

	/**
	 * Create the column definition for a small integer type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeSmallInteger(Fluent $column)
	{
		return 'smallint';
	}

	/**
	 * Create the column definition for a float type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeFloat(Fluent $column)
	{
		return 'real';
	}

	/**
	 * Create the column definition for a double type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeDouble(Fluent $column)
	{
		return 'double precision';
	}

	/**
	 * Create the column definition for a decimal type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeDecimal(Fluent $column)
	{
		return "decimal({$column->total}, {$column->places})";
	}

	/**
	 * Create the column definition for a boolean type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeBoolean(Fluent $column)
	{
		return 'boolean';
	}

	/**
	 * Create the column definition for an enum type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeEnum(Fluent $column)
	{
		$allowed = array_map(function($a) { return "'".$a."'"; }, $column->allowed);

		return "varchar(255) check (\"{$column->name}\" in (".implode(', ', $allowed)."))";
	}

	/**
	 * Create the column definition for a date type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeDate(Fluent $column)
	{
		return 'date';
	}

	/**
	 * Create the column definition for a date-time type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeDateTime(Fluent $column)
	{
		return 'timestamp';
	}
	
	/**
	 * Create the column definition for a date-time type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeDateTimeTz(Fluent $column)
	{
	    return 'timestamp(0) with time zone';
	}

	/**
	 * Create the column definition for a time type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeTime(Fluent $column)
	{
		return 'time';
	}
	
	/**
	 * Create the column definition for a time type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeTimeTz(Fluent $column)
	{
	    return 'time(0) with time zone';
	}

	/**
	 * Create the column definition for a timestamp type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeTimestamp(Fluent $column)
	{
	    if ($column->useCurrent) {
	        return 'timestamp default CURRENT_TIMESTAMP';
	    }
	    
		return 'timestamp';
	}
	
	/**
	 * Create the column definition for a timestamp type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeTimestampTz(Fluent $column)
	{
	    if ($column->useCurrent) {
	        return 'timestamp(0) with time zone default CURRENT_TIMESTAMP(0)';
	    }
	
	    return 'timestamp(0) with time zone';
	}

	/**
	 * Create the column definition for a binary type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeBinary(Fluent $column)
	{
		return 'bytea';
	}
	
	/**
	 * Create the column definition for a uuid type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeUuid(Fluent $column)
	{
	    return 'uuid';
	}
	
	/**
	 * Create the column definition for an IP address type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeIpAddress(Fluent $column)
	{
	    return 'inet';
	}
	
	/**
	 * Create the column definition for a MAC address type.
	 *
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string
	 */
	protected function typeMacAddress(Fluent $column)
	{
	    return 'macaddr';
	}

	/**
	 * Get the SQL for a nullable column modifier.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string|null
	 */
	protected function modifyNullable(Blueprint $blueprint, Fluent $column)
	{
		return $column->nullable ? ' null' : ' not null';
	}

	/**
	 * Get the SQL for a default column modifier.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string|null
	 */
	protected function modifyDefault(Blueprint $blueprint, Fluent $column)
	{
		if ( ! is_null($column->default))
		{
			return " default ".$this->getDefaultValue($column->default);
		}
	}

	/**
	 * Get the SQL for an auto-increment column modifier.
	 *
	 * @param  \Royalcms\Component\Database\Schema\Blueprint  $blueprint
	 * @param  \Royalcms\Component\Support\Fluent  $column
	 * @return string|null
	 */
	protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
	{
		if (in_array($column->type, $this->serials) && $column->autoIncrement)
		{
			return ' primary key';
		}
	}

}
