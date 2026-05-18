<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
    use HasFactory;
    protected $fillable = ['entity','level','action_id'];
    protected $primaryKey = ['entity','level','action_id'];
    public $incrementing = false;
    protected $keyType = 'string';

    public $table="permissions";

    public function save(array $options = [])
    {
        if (!$this->exists) {
            $query = static::query();

            foreach ($this->primaryKey as $key) {
                $query->where($key, '=', $this->getAttribute($key));
            }

            $this->exists = $query->exists();
        }

        return parent::save($options);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->primaryKey as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }

        return $query;
    }

    /**
     * Perform a model update operation.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return bool
     */
    protected function performUpdate(Builder $query)
    {
        return $this->setKeysForSaveQuery($query)->update($this->attributes);
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'level', 'level');
    }
}
