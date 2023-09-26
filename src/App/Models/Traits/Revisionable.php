<?php namespace Redooor\Redminportal\App\Models\Traits;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Revision;

trait Revisionable
{
    /**
     * @var array
     */
    private $originalData = array();
    
    /**
     * @var array
     */
    private $updatedData = array();
    
    /**
     * @var boolean
     */
    private $updating = false;
    
    /**
     * Keeps the list of values that have been updated
     *
     * @var array
     */
    protected $dirtyData = array();
    
    /**
     * Morph to Revision
     **/
    public function revisions()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Revision', 'revisionable');
    }
    
    /**
     * Create the event listeners for the saving and saved events
     * This lets us save revisions whenever a save is made, no matter the
     * http method.
     *
     */
    public static function bootRevisionable()
    {
        static::updating(function ($model) {
            $model->preSave();
        });
        
        static::updated(function ($model) {
            $model->postSave();
        });
        
        static::created(function ($model) {
            $model->postCreate();
        });
        
        static::deleted(function ($model) {
            $model->preSave();
            $model->postDelete();
        });
    }
    
    /**
    * Invoked before a model is saved. Return false to abort the operation.
    *
    * @return bool
    */
    public function preSave()
    {
        $this->originalData = $this->original;
        $this->updatedData = $this->attributes;
        
        // Disregard all object based items, like DateTime
        foreach ($this->updatedData as $key => $val) {
            if (gettype($val) == 'object' && !method_exists($val, '__toString')) {
                unset($this->originalData[$key]);
                unset($this->updatedData[$key]);
            }
        }
        
        $this->dirtyData = $this->getDirty();
        $this->updating = $this->exists;
    }
    
    /**
     * Called after a model is successfully saved.
     *
     * @return void
     */
    public function postSave()
    {
        // Check for revised fields
        $changes_to_record = $this->changedRevisionableFields();
        
        $revisions = array();
        
        // Loop through all attributes and create a new revision record each
        foreach (array_keys($changes_to_record) as $key) {
            $revisions[] = array(
                'revisionable_type' => get_class($this),
                'revisionable_id' => $this->getKey(),
                'attribute' => $key,
                'old_value' => array_get($this->originalData, $key),
                'new_value' => $this->updatedData[$key],
                'user_id' => $this->getUserId(),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            );
        }
        
        if (count($revisions) > 0) {
            $revision = new Revision;
            DB::table($revision->getTable())->insert($revisions);
        }
    }
    
    /**
    * Called after record successfully created
    */
    public function postCreate()
    {
        $revisions[] = array(
            'revisionable_type' => get_class($this),
            'revisionable_id' => $this->getKey(),
            'attribute' => 'created_at',
            'old_value' => null,
            'new_value' => $this->created_at,
            'user_id' => $this->getUserId(),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        );
        $revision = new Revision;
        DB::table($revision->getTable())->insert($revisions);
    }
    
    /**
     * If softdeletes are enabled, store the deleted time
     */
    public function postDelete()
    {
        // If model is softDelete
        if (isset($this->isSoftDelete) and $this->isSoftDelete) {
            $revisions[] = array(
                'revisionable_type' => get_class($this),
                'revisionable_id' => $this->getKey(),
                'attribute' => 'deleted_at',
                'old_value' => null,
                'new_value' => $this->deleted_at,
                'user_id' => $this->getUserId(),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            );
            $revision = new Revision;
            DB::table($revision->getTable())->insert($revisions);
        } else {
            // Delete all associated revisions
            $this->revisions()->delete();
        }
    }
    
    /**
     * Get all of the changes that have been made, that are also supposed
     * to have their changes recorded
     *
     * @return array fields with new data, that should be recorded
     */
    private function changedRevisionableFields()
    {
        $changes_to_record = array();
        
        foreach ($this->dirtyData as $key => $value) {
            if (!is_array($value)) {
                if (!isset($this->originalData[$key]) || $this->originalData[$key] != $this->updatedData[$key]) {
                    $changes_to_record[$key] = $value;
                }
            }
        }
        
        return $changes_to_record;
    }
    
    /**
     * Attempt to find the user id of the currently logged in user
     * Supports Cartalyst Sentry/Sentinel based authentication, as well as stock Auth
     **/
    public function getUserId()
    {
        try {
            if (class_exists($class = '\SleepingOwl\AdminAuth\Facades\AdminAuth')
                || class_exists($class = '\Cartalyst\Sentry\Facades\Laravel\Sentry')
                || class_exists($class = '\Cartalyst\Sentinel\Laravel\Facades\Sentinel')
            ) {
                return ($class::check()) ? $class::getUser()->id : null;
            } elseif (Auth::guard('redminguard')->check()) {
                return Auth::guard('redminguard')->user()->getAuthIdentifier();
            }
        } catch (Exception $e) {
            return null;
        }
        
        return null;
    }
}
